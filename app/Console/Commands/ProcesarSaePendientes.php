<?php

namespace App\Console\Commands;

use App\Models\PedidoSaePendiente;
use App\Models\PedidoWeb;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Procesa la cola de pedidos SAE que el carrito no logro insertar tras 5
 * reintentos en el frontend. Los reintenta uno por uno contra
 * /catalowari/api/guardar_v2 hasta logralos o agotar MAX_INTENTOS adicionales.
 *
 * Uso manual:
 *   php artisan pedidos:procesar-sae-pendientes
 *
 * Automatico via app/Console/Kernel.php (cada 5 min).
 */
class ProcesarSaePendientes extends Command
{
    protected $signature   = 'pedidos:procesar-sae-pendientes
                              {--limit=50 : Maximo de pendientes a procesar por corrida}
                              {--dry-run : Solo lista los pendientes sin enviarlos}';
    protected $description = 'Reintenta insertar en SAE los pedidos que la cola dejo pendientes';

    private const URL_SAE          = 'https://sistemasowari.com:8443/catalowari/api/guardar_v2';
    private const TIMEOUT_SEGUNDOS = 30;

    public function handle(): int
    {
        $limit  = intval($this->option('limit'));
        $dryRun = $this->option('dry-run');

        // Tomar pendientes O en_proceso colgados (>30 min sin update)
        $pendientes = PedidoSaePendiente::where(function($q) {
                $q->where('estado', PedidoSaePendiente::ESTADO_PENDIENTE)
                  ->orWhere(function($qq) {
                      $qq->where('estado', PedidoSaePendiente::ESTADO_EN_PROCESO)
                         ->where('updated_at', '<', now()->subMinutes(30));
                  });
            })
            ->where('intentos', '<', PedidoSaePendiente::MAX_INTENTOS)
            ->orderBy('id', 'asc')
            ->limit($limit)
            ->get();

        if ($pendientes->isEmpty()) {
            $this->info('No hay pedidos SAE pendientes.');
            return self::SUCCESS;
        }

        $this->info('Procesando ' . $pendientes->count() . ' pedidos SAE pendientes' . ($dryRun ? ' (dry-run)' : '') . '...');

        $exitos  = 0;
        $errores = 0;

        foreach ($pendientes as $pendiente) {
            if ($dryRun) {
                $this->line(sprintf(
                    '  #%d  cliente=%s  empresa=%d  intentos=%d  estado=%s  ult_error=%s',
                    $pendiente->id,
                    $pendiente->cliente,
                    $pendiente->empresa,
                    $pendiente->intentos,
                    $pendiente->estado,
                    str($pendiente->ultimo_error ?? '')->limit(50)
                ));
                continue;
            }

            // Lock optimista: marcar en_proceso
            $pendiente->estado = PedidoSaePendiente::ESTADO_EN_PROCESO;
            $pendiente->save();

            $resultado = $this->intentarInsercion($pendiente);

            if ($resultado['exito']) {
                $pendiente->fill([
                    'estado'       => PedidoSaePendiente::ESTADO_COMPLETADO,
                    'folio_sae'    => $resultado['pedido'],
                    'completed_at' => now(),
                    'intentos'     => $pendiente->intentos + 1,
                ])->save();

                // Si hay PedidoWeb relacionado y aun no tiene folio, actualizarlo
                $this->actualizarEspejoLocal($pendiente, $resultado['pedido']);

                $this->info(sprintf('  ✓ #%d empresa=%d folio=%s', $pendiente->id, $pendiente->empresa, $resultado['pedido']));
                $exitos++;
            } else {
                $intentos = $pendiente->intentos + 1;
                $estadoFinal = $intentos >= PedidoSaePendiente::MAX_INTENTOS
                    ? PedidoSaePendiente::ESTADO_FALLIDO
                    : PedidoSaePendiente::ESTADO_PENDIENTE;

                $pendiente->fill([
                    'estado'       => $estadoFinal,
                    'intentos'     => $intentos,
                    'ultimo_error' => $resultado['error'],
                ])->save();

                $this->warn(sprintf('  ✗ #%d empresa=%d intentos=%d/%d error=%s',
                    $pendiente->id,
                    $pendiente->empresa,
                    $intentos,
                    PedidoSaePendiente::MAX_INTENTOS,
                    $resultado['error']
                ));
                $errores++;
            }
        }

        if (!$dryRun) {
            $this->info("Listo. Exitos: $exitos | Errores: $errores");
            Log::info('procesar-sae-pendientes', ['exitos' => $exitos, 'errores' => $errores]);
        }

        return self::SUCCESS;
    }

    /**
     * Hace el POST a guardar_v2 con el payload almacenado.
     * @return array{exito: bool, pedido?: string, error?: string}
     */
    private function intentarInsercion(PedidoSaePendiente $pendiente): array
    {
        $payload = $pendiente->payload;

        $ch = curl_init(self::URL_SAE);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT        => self::TIMEOUT_SEGUNDOS,
        ]);

        $body   = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err    = curl_error($ch);
        curl_close($ch);

        if ($body === false || $status === 0) {
            return ['exito' => false, 'error' => 'cURL: ' . $err];
        }

        if ($status >= 400) {
            return ['exito' => false, 'error' => 'HTTP ' . $status . ': ' . substr($body, 0, 500)];
        }

        $data = json_decode($body, true);
        if (!is_array($data) || ($data['code'] ?? 0) !== 1 || empty($data['pedido'])) {
            return ['exito' => false, 'error' => 'SAE rechazo: ' . ($data['mensaje'] ?? substr($body, 0, 500))];
        }

        return ['exito' => true, 'pedido' => $data['pedido']];
    }

    /**
     * Si la pendiente esta enlazada a un PedidoWeb (id_pedido_web) actualiza
     * la columna correspondiente segun la empresa:
     *   - empresa 1 (factura)  → pedido_sae          (existente, semantica preservada)
     *   - empresa 3 (remision) → pedido_sae_remision (campo agregado en v2)
     * Solo escribe si la columna esta vacia (no pisa folios ya logrados).
     */
    private function actualizarEspejoLocal(PedidoSaePendiente $pendiente, string $folio): void
    {
        if (!$pendiente->id_pedido_web) return;

        $espejo = PedidoWeb::find($pendiente->id_pedido_web);
        if (!$espejo) return;

        $columna = ($pendiente->empresa == 3) ? 'pedido_sae_remision' : 'pedido_sae';
        $valorActual = $espejo->{$columna} ?? null;

        if (empty($valorActual) || $valorActual === '0' || $valorActual === 0) {
            $espejo->{$columna} = $folio;
            $espejo->save();
        }
    }
}

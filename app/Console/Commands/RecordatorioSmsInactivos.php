<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AltiriaSms;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Envia un SMS recordatorio (via Altiria) a los clientes con telefono que
 * llevan 15 dias o mas sin generar un pedido en la tienda en linea.
 *
 * Reglas:
 *   - El cliente debe tener telefono y NO estar suspendido (a los 30 dias el
 *     cron clientes:suspender-inactivos lo suspende y ya ve el banner).
 *   - Se manda UNA sola vez por ciclo: la marca users.sms_recordatorio_enviado_at
 *     evita reenviarlo dia tras dia. Cuando el cliente vuelve a comprar, su
 *     ultimo pedido queda mas reciente que la marca y, si vuelve a cumplir 15
 *     dias inactivo, se le envia de nuevo.
 *   - Clientes que nunca han pedido (sin filas en pedidos_web) quedan fuera.
 *
 * Uso:  php artisan clientes:recordatorio-sms [--dry-run]
 * Cron: diario a las 10:00 (horario habil para no molestar de noche).
 */
class RecordatorioSmsInactivos extends Command
{
    protected $signature   = 'clientes:recordatorio-sms
                              {--dry-run : Lista a quien se enviaria sin mandar SMS ni tocar la BD}';
    protected $description  = 'Envia SMS recordatorio a clientes con telefono y 15+ dias sin pedido';

    private const DIAS_INACTIVO = 15;

    private const MENSAJE = 'En OWARI valoramos tu compra, han pasado 15 dias desde que tu carrito no ha sido utilizado entra a https://owari.com.mx a tu tienda en linea y continua';

    public function handle(AltiriaSms $sms): int
    {
        $dryRun = $this->option('dry-run');

        // Ultima compra por cliente, solo los que llevan >= 15 dias inactivos.
        $ultimaCompraPorCliente = DB::table('pedidos_web')
            ->select('cliente', DB::raw('MAX(created_at) as ultima_compra'))
            ->whereNull('deleted_at')
            ->groupBy('cliente')
            ->havingRaw('(CURRENT_DATE - MAX(created_at)::date) >= ' . self::DIAS_INACTIVO)
            ->pluck('ultima_compra', 'cliente')
            ->all();

        if (empty($ultimaCompraPorCliente)) {
            $this->info('No hay clientes con ' . self::DIAS_INACTIVO . '+ dias de inactividad.');
            return self::SUCCESS;
        }

        // Usuarios cliente, con telefono, no suspendidos, dentro de esa lista.
        $usuarios = User::where('cliente', true)
            ->where('cuenta_suspendida', false)
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->whereIn('clave_cliente', array_keys($ultimaCompraPorCliente))
            ->get(['id', 'clave_cliente', 'name', 'phone', 'sms_recordatorio_enviado_at']);

        // Filtrar a los que aun NO se les envio en este ciclo (la marca es
        // anterior a su ultima compra, o nunca se les ha enviado).
        $destinatarios = $usuarios->filter(function ($u) use ($ultimaCompraPorCliente) {
            $ultimaCompra = Carbon::parse($ultimaCompraPorCliente[$u->clave_cliente]);
            return $u->sms_recordatorio_enviado_at === null
                || $u->sms_recordatorio_enviado_at->lt($ultimaCompra);
        })->values();

        if ($destinatarios->isEmpty()) {
            $this->info('Todos los clientes inactivos con telefono ya fueron recordados en este ciclo.');
            return self::SUCCESS;
        }

        $this->info($destinatarios->count() . ' clientes a recordar' . ($dryRun ? ' [dry-run]' : '') . ':');

        $enviados = 0;
        $fallidos = 0;

        foreach ($destinatarios as $u) {
            if ($dryRun) {
                $this->line(sprintf('  - %s %s tel=%s', $u->clave_cliente, $u->name, $u->phone));
                continue;
            }

            $res = $sms->enviar($u->phone, self::MENSAJE);

            if ($res['ok']) {
                $u->forceFill(['sms_recordatorio_enviado_at' => now()])->save();
                $this->info(sprintf('  OK  %s %s tel=%s', $u->clave_cliente, $u->name, $u->phone));
                $enviados++;
            } else {
                $this->warn(sprintf('  ERR %s %s tel=%s -> %s', $u->clave_cliente, $u->name, $u->phone, $res['error']));
                $fallidos++;
            }
        }

        if (!$dryRun) {
            Log::info('clientes:recordatorio-sms', ['enviados' => $enviados, 'fallidos' => $fallidos]);
            $this->info("Listo. Enviados: {$enviados} | Fallidos: {$fallidos}");

            // SMS de resumen al admin para validar el envio. Solo si hubo
            // intentos de envio (enviados o fallidos > 0).
            if (($enviados + $fallidos) > 0) {
                $this->enviarResumen($sms, $destinatarios->count(), $enviados, $fallidos);
            }
        }

        return self::SUCCESS;
    }

    /**
     * Manda un SMS de resumen al numero admin configurado (ALTIRIA_RESUMEN_TEL).
     * Un fallo aqui no interrumpe el comando: solo se loguea.
     */
    private function enviarResumen(AltiriaSms $sms, int $total, int $enviados, int $fallidos): void
    {
        $tel = config('services.altiria.resumen_tel');
        if (empty($tel)) {
            return;
        }

        $resumen = sprintf(
            'OWARI recordatorios %s: %d enviados, %d fallidos de %d clientes.',
            now()->format('d/m/Y'),
            $enviados,
            $fallidos,
            $total
        );

        $res = $sms->enviar($tel, $resumen);
        if ($res['ok']) {
            $this->info("Resumen enviado a {$tel}.");
        } else {
            $this->warn("No se pudo enviar el resumen a {$tel}: {$res['error']}");
            Log::warning('clientes:recordatorio-sms resumen fallo', ['tel' => $tel, 'error' => $res['error']]);
        }
    }
}

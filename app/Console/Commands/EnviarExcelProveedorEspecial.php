<?php

namespace App\Console\Commands;

use App\Exports\PedidoEspecialProveedorExport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Envia el Excel REDUCIDO al proveedor especial, DESACOPLADO del checkout.
 *
 * Antes esto se enviaba en linea al guardar el pedido especial, pero con
 * QUEUE=sync ese envio SMTP extra colgaba el checkout ("se queda atorado").
 * Ahora corre por cron: busca pedidos especiales cuyo proveedor tenga
 * `enviar_excel = true` y `correo` en SOMA (proveedores_especiales) y que aun
 * no se hayan enviado (pedidos_especiales.correo_proveedor_enviado_at IS NULL),
 * reconstruye el Excel reducido desde la BD y lo manda.
 *
 * Columnas: CLIENTE, PEDIDO ESPECIAL, CLAVE, CANTIDAD, CLAVE <nombre_proveedor>.
 *
 * Requiere la columna pedidos_especiales.correo_proveedor_enviado_at (timestamp
 * null). Si no existe, el comando falla de forma controlada y lo registra.
 *
 * Uso:  php artisan especiales:enviar-excel-proveedor [--dry-run]
 * Cron: cada 5 minutos (App\Console\Kernel::schedule).
 */
class EnviarExcelProveedorEspecial extends Command
{
    protected $signature   = 'especiales:enviar-excel-proveedor {--dry-run : Solo lista, no envia ni marca}';
    protected $description  = 'Envia el Excel reducido a proveedores especiales con correo/enviar_excel en SOMA';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        // Config de proveedores especiales (SOMA): mapa clave => {nombre, correo, enviar_excel}.
        try {
            $cfgRows = DB::connection('owari_soma')->table('proveedores_especiales')
                ->where('activo', true)
                ->get(['clave', 'nombre', 'correo', 'enviar_excel']);
        } catch (\Throwable $e) {
            Log::warning('especiales:enviar-excel-proveedor: no se pudo leer proveedores_especiales: ' . $e->getMessage());
            $this->error('No se pudo leer proveedores_especiales de SOMA: ' . $e->getMessage());
            return self::FAILURE;
        }
        $cfgMap = [];
        foreach ($cfgRows as $r) $cfgMap[$r->clave] = $r;

        // Pedidos especiales pendientes de enviar al proveedor (ultimos dias).
        try {
            $pedidos = DB::table('pedidos_especiales')
                ->whereNotNull('clave_proveedor')
                ->whereNull('correo_proveedor_enviado_at')
                ->whereNull('deleted_at')
                ->where('created_at', '>=', now()->subDays(3))
                ->orderBy('id')
                ->limit(50)
                ->get(['id', 'cliente', 'clave_proveedor', 'created_at']);
        } catch (\Throwable $e) {
            Log::warning('especiales:enviar-excel-proveedor: falta columna correo_proveedor_enviado_at? ' . $e->getMessage());
            $this->error('Query fallo (¿falta pedidos_especiales.correo_proveedor_enviado_at?): ' . $e->getMessage());
            return self::FAILURE;
        }

        if ($pedidos->isEmpty()) {
            $this->info('No hay pedidos especiales pendientes de enviar al proveedor.');
            return self::SUCCESS;
        }

        $enviados = 0;
        foreach ($pedidos as $pedido) {
            try {
                $cfg = $cfgMap[$pedido->clave_proveedor] ?? null;

                // Proveedor sin envio configurado: se marca como procesado para
                // no re-evaluarlo en cada corrida.
                if (!$cfg || empty($cfg->enviar_excel) || empty($cfg->correo)) {
                    if (!$dryRun) $this->marcarEnviado($pedido->id);
                    continue;
                }

                $destinos = array_values(array_filter(array_map('trim', explode(',', $cfg->correo))));
                if (empty($destinos)) {
                    if (!$dryRun) $this->marcarEnviado($pedido->id);
                    continue;
                }

                $nombreProveedor = !empty($cfg->nombre) ? $cfg->nombre : $pedido->clave_proveedor;

                $partidas = DB::table('pedidos_especiales_partidas')
                    ->where('id_pedido', $pedido->id)
                    ->whereNull('deleted_at')
                    ->get(['clave', 'cantidad']);

                // Excel reducido (mismo contenido, recortado).
                $reducido = [[
                    'CLIENTE', 'PEDIDO ESPECIAL', 'CLAVE', 'CANTIDAD', 'CLAVE ' . $nombreProveedor,
                ]];
                foreach ($partidas as $p) {
                    $reducido[] = [
                        $pedido->cliente,
                        $pedido->id,
                        $p->clave,
                        (int) $p->cantidad,
                        $this->claveProveedorDe($p->clave),
                    ];
                }

                if ($dryRun) {
                    $this->line("[dry-run] pedido {$pedido->id} ({$nombreProveedor}) -> " . implode(', ', $destinos));
                    continue;
                }

                $archivo = date('YmdHis') . '_prov_' . $pedido->id . '.xlsx';
                Excel::store(new PedidoEspecialProveedorExport($reducido), 'pedidos_especiales/' . $archivo);

                \Mail::send('emails.pedido_especial_proveedor', ['pedido' => $pedido, 'nombreProveedor' => $nombreProveedor],
                    function ($message) use ($pedido, $archivo, $destinos, $nombreProveedor) {
                        $message->from('pedido_especial@owari.com.mx', 'Pedido para surtir');
                        $message->subject('Pedido para surtir ' . $nombreProveedor . ' #' . $pedido->id);
                        $message->attach(storage_path() . '/app/pedidos_especiales/' . $archivo);
                        $message->to($destinos);
                    });

                $this->marcarEnviado($pedido->id);
                $enviados++;
                $this->line("Enviado pedido {$pedido->id} a " . implode(', ', $destinos));
            } catch (\Throwable $e) {
                // No se marca enviado: se reintenta en la proxima corrida.
                Log::warning('especiales:enviar-excel-proveedor: fallo pedido ' . $pedido->id . ': ' . $e->getMessage());
                $this->error("Fallo pedido {$pedido->id}: " . $e->getMessage());
            }
        }

        Log::info('especiales:enviar-excel-proveedor', ['enviados' => $enviados]);
        $this->info("Listo. Enviados: {$enviados}.");
        return self::SUCCESS;
    }

    private function marcarEnviado(int $id): void
    {
        DB::table('pedidos_especiales')->where('id', $id)->update(['correo_proveedor_enviado_at' => now()]);
    }

    /** Clave del producto en el catalogo de su proveedor principal (SOMA). */
    private function claveProveedorDe(string $claveProducto): string
    {
        try {
            $row = DB::connection('owari_soma')->select("
                SELECT pp.clave_proveedor
                FROM productos p
                LEFT JOIN productos_proveedores pp
                       ON pp.id_producto  = p.id
                      AND pp.id_proveedor = p.id_proveedor
                      AND pp.deleted_at IS NULL
                WHERE p.clave = ? AND p.deleted_at IS NULL
                LIMIT 1
            ", [$claveProducto]);
            return ($row[0]->clave_proveedor ?? null) ?: 'SIN CLAVE';
        } catch (\Throwable $e) {
            return 'SIN CLAVE';
        }
    }
}

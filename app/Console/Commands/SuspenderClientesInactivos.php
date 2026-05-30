<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Suspende los clientes (users.cliente = true) que tienen mas de 30 dias sin
 * generar un pedido en la tienda en linea. Replica el query del negocio:
 *
 *   SELECT cliente, MAX(created_at) AS ultima_compra
 *   FROM pedidos_web
 *   GROUP BY cliente
 *   HAVING MAX(created_at) < CURRENT_DATE - INTERVAL '30 days'
 *
 * Para cada cliente encontrado, marca users.cuenta_suspendida = true.
 *
 * Politica explicita: NO desbloquea automaticamente. El desbloqueo es manual
 * (admin desde /clientes/{id}/editar). Tambien NO toca clientes que nunca
 * han generado un pedido (cliente nuevo) — el HAVING los excluye porque no
 * estan en pedidos_web.
 *
 * Uso:   php artisan clientes:suspender-inactivos
 * Cron:  todas las noches a las 02:00 (ver App\Console\Kernel::schedule).
 */
class SuspenderClientesInactivos extends Command
{
    protected $signature   = 'clientes:suspender-inactivos
                              {--dry-run : Solo lista los clientes a suspender, no actualiza}';
    protected $description = 'Marca cuenta_suspendida=true en clientes con mas de 30 dias sin pedidos_web';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $clavesInactivas = DB::table('pedidos_web')
            ->select('cliente')
            ->whereNull('deleted_at')
            ->groupBy('cliente')
            ->havingRaw('MAX(created_at) < CURRENT_DATE - INTERVAL \'30 days\'')
            ->pluck('cliente')
            ->all();

        if (empty($clavesInactivas)) {
            $this->info('No hay clientes con mas de 30 dias sin pedidos.');
            return self::SUCCESS;
        }

        // Tomar solo usuarios que sean cliente y que aun no esten suspendidos.
        $usuarios = User::where('cliente', true)
            ->where('cuenta_suspendida', false)
            ->whereIn('clave_cliente', $clavesInactivas)
            ->get(['id', 'clave_cliente', 'name', 'email']);

        if ($usuarios->isEmpty()) {
            $this->info('Los ' . count($clavesInactivas) . ' clientes con inactividad ya estan suspendidos. Nada por hacer.');
            return self::SUCCESS;
        }

        $this->info(count($usuarios) . ' clientes seran ' . ($dryRun ? '[dry-run] suspendidos' : 'suspendidos') . ':');
        foreach ($usuarios as $u) {
            $this->line(sprintf('  - %s %s (%s)', $u->clave_cliente, $u->name, $u->email));
        }

        if ($dryRun) {
            return self::SUCCESS;
        }

        $afectados = User::where('cliente', true)
            ->where('cuenta_suspendida', false)
            ->whereIn('clave_cliente', $clavesInactivas)
            ->update(['cuenta_suspendida' => true]);

        Log::info('clientes:suspender-inactivos', ['suspendidos' => $afectados]);
        $this->info("Listo. {$afectados} clientes marcados como suspendidos.");

        return self::SUCCESS;
    }
}

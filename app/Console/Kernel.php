<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // DESACTIVADO 2026-07-03 (por solicitud del cliente, no es necesario):
        // Reintenta los pedidos SAE que el carrito dejo en cola tras
        // sus 5 reintentos en frontend.
        // $schedule->command('pedidos:procesar-sae-pendientes')
        //          ->everyFiveMinutes()
        //          ->withoutOverlapping()
        //          ->runInBackground();

        // FASE 1 migracion-tienda: la suspension por inactividad ahora la corre
        // SOMA (tienda:suspender-inactivos, 02:30) sobre clientes_accesos. El
        // comando local sigue disponible a mano, pero ya no esta programado.
        // $schedule->command('clientes:suspender-inactivos')
        //          ->dailyAt('02:00')
        //          ->withoutOverlapping();

        // Regenera el Excel de promociones globales de la tienda a las 02:00,
        // que el cliente descarga desde /tienda_online/promociones.xlsx.
        $schedule->command('promociones:generar-excel')
                 ->dailyAt('02:00')
                 ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

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
        // Reintenta los pedidos SAE que el carrito dejo en cola tras
        // sus 5 reintentos en frontend.
        $schedule->command('pedidos:procesar-sae-pendientes')
                 ->everyFiveMinutes()
                 ->withoutOverlapping()
                 ->runInBackground();

        // Marca como suspendidos los clientes que llevan mas de 30 dias sin
        // generar pedidos en la tienda en linea. Corre todas las noches a
        // las 02:00 para no chocar con la actividad del dia.
        $schedule->command('clientes:suspender-inactivos')
                 ->dailyAt('02:00')
                 ->withoutOverlapping();

        // Envia SMS recordatorio (Altiria) a clientes con telefono y 15+ dias
        // sin pedido. A las 10:00 (horario habil) para no molestar de noche.
        $schedule->command('clientes:recordatorio-sms')
                 ->dailyAt('10:00')
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

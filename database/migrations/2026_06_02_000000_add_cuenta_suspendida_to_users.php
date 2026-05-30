<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Marca al cliente como suspendido por inactividad. Cuando cuenta_suspendida
     * = true, el middleware VerificarCuentaSuspendida redirige todo el flujo de
     * la tienda en linea a la pantalla con el banner. Se setea automaticamente
     * cada noche por el cron `clientes:suspender-inactivos` y se desbloquea
     * manualmente desde la vista admin /clientes/{id}/editar.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('cuenta_suspendida')->default(false)->after('cliente');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('cuenta_suspendida');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Momento en que se le envio al cliente el SMS recordatorio por inactividad
     * (15 dias sin pedido). Sirve para no reenviarlo en el mismo ciclo: solo se
     * vuelve a mandar cuando el cliente compra de nuevo (su ultimo pedido queda
     * mas reciente que esta marca) y vuelve a cumplir 15 dias inactivo.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('sms_recordatorio_enviado_at')->nullable()->after('cuenta_suspendida');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('sms_recordatorio_enviado_at');
        });
    }
};

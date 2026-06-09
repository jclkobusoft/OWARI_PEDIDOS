<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Marca el momento en que ventas reactivo manualmente la cuenta de un
     * cliente que fue suspendido por el cron `clientes:suspender-inactivos`.
     * El cron usa este timestamp para darle al cliente una ventana de gracia
     * (5 dias) tras la reactivacion para generar un nuevo pedido antes de
     * volver a evaluarlo. Si en esos 5 dias compra, su MAX(created_at) en
     * pedidos_web deja de ser >30 dias y sale del query naturalmente. Si no
     * compra, el cron lo vuelve a suspender.
     *
     * NULL = nunca reactivado, o se rompio el ciclo por una suspension manual.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('reactivated_at')->nullable()->after('cuenta_suspendida');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('reactivated_at');
        });
    }
};

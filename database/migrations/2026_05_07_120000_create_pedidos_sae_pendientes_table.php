<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cola de pedidos que el frontend del carrito intento insertar en SAE pero
 * cuyas 5 reintentos fallaron. Un comando artisan procesa esta tabla cada
 * pocos minutos hasta que SAE acepte el pedido o se marquen como fallidos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos_sae_pendientes', function (Blueprint $table) {
            $table->id();

            // Datos del pedido a insertar (los mismos que recibe guardar_v2)
            $table->string('cliente', 50)->index();
            $table->smallInteger('empresa');                    // 1 (factura) o 3 (remision)
            $table->jsonb('payload');                           // shape de guardar_v2: {empresa, cliente, usuario, su_pedido, partidas}

            // Trazabilidad
            $table->unsignedInteger('intentos')->default(0);
            $table->text('ultimo_error')->nullable();
            $table->string('estado', 20)->default('pendiente'); // pendiente | en_proceso | completado | fallido
            $table->string('folio_sae', 50)->nullable();        // ej. "4CW12345" cuando se logre
            $table->unsignedBigInteger('id_pedido_web')->nullable();

            $table->timestamps();
            $table->timestamp('completed_at')->nullable();

            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos_sae_pendientes');
    }
};

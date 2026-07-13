<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Carrito de la tienda en linea persistido en base de datos (antes vivia solo
 * en la sesion de archivo del servidor). Una fila por producto del carrito,
 * ligada al cliente (user_id) para tener control de la informacion.
 *
 * `tipo` distingue el carrito normal del especial. `datos` guarda el item
 * completo tal cual lo usaba la sesion (incluye 'partida', 'sustituto', etc.)
 * para no romper vistas ni checkout.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carrito_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->string('tipo', 20);                // 'normal' | 'especial'
            $table->string('numero_parte', 191);
            $table->integer('cantidad')->default(0);
            $table->jsonb('datos');                    // item completo (partida incluida)
            $table->timestamps();

            $table->unique(['user_id', 'tipo', 'numero_parte']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carrito_items');
    }
};

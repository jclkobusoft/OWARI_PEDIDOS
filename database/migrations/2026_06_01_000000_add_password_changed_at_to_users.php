<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Marca el momento en que el usuario cambio su contraseña inicial generica.
     * Null = nunca la ha cambiado → el middleware ForzarCambioPassword lo
     * redirige a la pantalla de cambio cuando entra a la tienda. Con fecha =
     * ya esta usando una contraseña propia.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('password_changed_at')->nullable()->after('password');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password_changed_at');
        });
    }
};

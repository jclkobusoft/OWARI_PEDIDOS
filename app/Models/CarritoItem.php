<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Un producto del carrito de la tienda, ligado al cliente (user_id).
 * `datos` es el item completo (misma forma que tenia en la sesion).
 */
class CarritoItem extends Model
{
    protected $table = 'carrito_items';

    protected $fillable = [
        'user_id',
        'tipo',
        'numero_parte',
        'cantidad',
        'datos',
    ];

    protected $casts = [
        'datos' => 'array',
    ];
}

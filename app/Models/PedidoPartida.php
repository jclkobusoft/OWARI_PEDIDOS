<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PedidoPartida extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'pedidos_partidas';


     protected $fillable = [
        'id_pedido',
        'clave',
        'descripcion',
        'precio_unitario',
        'iva',
        'cantidad',
        'gran_total'
     ];

}

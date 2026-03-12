<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PedidoEspecialPartidaSae extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'pedidos_especiales_partidas_sae';


     protected $fillable = [
        'id_pedido',
        'clave',
        'precio_unitario',
        'cantidad',
        'gran_total',
        'surtido'
     ];

}

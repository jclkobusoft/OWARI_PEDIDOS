<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PedidoEspecialPartida extends Model
{
    use HasFactory;
    use SoftDeletes;

    // Unificado post-corte: los especiales viven en SOMA (historico +
    // tienda nueva + telemarketing comparten la misma tabla, como antes)
    protected $connection = 'owari_soma';

    protected $table = 'tienda_pedidos_especiales_partidas';


     protected $fillable = [
        'id_pedido',
        'clave',
        'precio_unitario',
        'cantidad',
        'gran_total',
        'surtido'
     ];

}

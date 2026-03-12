<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PedidoPendiente extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'pedidos_pendientes_web';


     protected $fillable = [
        'cliente',
        'gran_total',
        'partidas',
        'partidas_detalle',
        'estado',
        'telefono',
        'email',
        'partidas_especiales',
        'partidas_especiales_detalle',
        'fecha_recoge',
        'metodo_pago',
        'forma_pago',
        'uso_cfdi',
        'id_usuario'

     ];
}

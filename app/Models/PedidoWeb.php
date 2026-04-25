<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PedidoWeb extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'pedidos_web';


     protected $fillable = [
       'cliente',
       'subtotal',
       'iva',
       'gran_total',
       'cadena_original',
       'estado',
       'capturo',
       'pedido_sae',           // folio empresa 1 (factura). Se usa desde antes del refactor v2; semantica preservada.
       'pedido_sae_remision',  // folio empresa 3 (remision). NUEVO en refactor v2 cuando cliente tiene W en pos.4.
       'tiendita',
       'porcentaje'
     ];

     public function partidas()
    {
        // hasOne(RelatedModel, foreignKeyOnRelatedModel = user_id, localKey = id)
        return $this->hasMany('App\Models\PedidoPartida','id_pedido','id');
    }

}

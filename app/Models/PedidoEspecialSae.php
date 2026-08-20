<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PedidoEspecialSae extends Model
{
    use HasFactory;
    use SoftDeletes;

    // Unificado post-corte: los especiales viven en SOMA (historico +
    // tienda nueva + telemarketing comparten la misma tabla, como antes)
    protected $connection = 'owari_soma';

    protected $table = 'tienda_pedidos_especiales_sae';


     protected $fillable = [
       'cliente',
       'subtotal',
       'iva',
       'gran_total',
       'cadena_original',
       'capturo',
       'pedido_sae',
       'id_pedido_especial'
     ];

     public function partidas()
    {
        // hasOne(RelatedModel, foreignKeyOnRelatedModel = user_id, localKey = id)
        return $this->hasMany('App\Models\PedidoEspecialPartidaSae','id_pedido','id');
    }

    public function creador()
    {
        // hasOne(RelatedModel, foreignKeyOnRelatedModel = user_id, localKey = id)
        return $this->hasOne('App\Models\User','id','capturo');
    }

}

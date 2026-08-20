<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PedidoEspecial extends Model
{
    use HasFactory;
    use SoftDeletes;

    // Unificado post-corte: los especiales viven en SOMA (historico +
    // tienda nueva + telemarketing comparten la misma tabla, como antes)
    protected $connection = 'owari_soma';

    protected $table = 'tienda_pedidos_especiales';


     protected $fillable = [
       'cliente',
       'gran_total',
       'cadena_original',
       'capturo',
       'clave_proveedor',
     ];

     public function partidas()
    {
        // hasOne(RelatedModel, foreignKeyOnRelatedModel = user_id, localKey = id)
        return $this->hasMany('App\Models\PedidoEspecialPartida','id_pedido','id');
    }

    public function creador()
    {
        // hasOne(RelatedModel, foreignKeyOnRelatedModel = user_id, localKey = id)
        return $this->hasOne('App\Models\User','id','capturo');
    }

     public function generados()
    {
        // hasOne(RelatedModel, foreignKeyOnRelatedModel = user_id, localKey = id)
        return $this->hasMany('App\Models\PedidoEspecialSae','id_pedido_especial','id');
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoSaePendiente extends Model
{
    use HasFactory;

    protected $table = 'pedidos_sae_pendientes';

    protected $fillable = [
        'cliente',
        'empresa',
        'payload',
        'intentos',
        'ultimo_error',
        'estado',
        'folio_sae',
        'id_pedido_web',
        'completed_at',
    ];

    protected $casts = [
        'payload'      => 'array',
        'empresa'      => 'integer',
        'intentos'     => 'integer',
        'completed_at' => 'datetime',
    ];

    // Estados posibles
    public const ESTADO_PENDIENTE  = 'pendiente';
    public const ESTADO_EN_PROCESO = 'en_proceso';
    public const ESTADO_COMPLETADO = 'completado';
    public const ESTADO_FALLIDO    = 'fallido';

    public const MAX_INTENTOS = 20;
}

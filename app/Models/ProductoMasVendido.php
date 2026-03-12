<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoMasVendido extends Model
{
    use HasFactory;

    protected $table="productos_mas_vendidos";

    protected $fillable = [
        'clave'
    ];
}

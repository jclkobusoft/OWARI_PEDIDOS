<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Etiqueta extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'etiquetas';


     protected $fillable = [
        'pedido',
        'cliente',
        'data'
     ];

}

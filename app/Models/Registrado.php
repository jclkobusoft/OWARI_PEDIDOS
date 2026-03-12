<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registrado extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'registrados';


     protected $fillable = [
        'nombre',
        'email',
        'telefono',
        'estado',
        'id_usuario',
        'cliente'
     ];

}

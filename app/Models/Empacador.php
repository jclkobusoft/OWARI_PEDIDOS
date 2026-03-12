<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empacador extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'empacadores';


     protected $fillable = [
        'iniciales',
        'nombre'
     ];

}

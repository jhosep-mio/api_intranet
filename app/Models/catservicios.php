<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class catservicios extends Model
{
    use HasFactory;
    protected $fillable = [
        'insumoUSB', 
        'nombre', 
        'impreso'
    ];
}

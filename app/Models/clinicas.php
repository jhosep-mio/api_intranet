<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class clinicas extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre', 
        'direccion', 
        'referencia', 
        'telefono', 
        'celular'
    ];
}

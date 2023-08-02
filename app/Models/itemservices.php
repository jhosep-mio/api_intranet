<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class itemservices extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_servicio', 
        'nombre', 
        'precio_impresion', 
        'precio_digital', 
        'comision_impreso', 
        'comision_digital',
        'insumos1', 
        'insumos2',
        'insumos3',
        'insumos4',
        'insumosM1',
        'insumosM2',
        'insumosM3',
        'insumosM4',
        'insumosM5',
        'insumosM6',
        'insumoCarpeta',
    ];
}

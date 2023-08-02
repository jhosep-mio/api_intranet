<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ordenes extends Model
{
    use HasFactory;

    public function odontologo()
    {
        return $this->belongsTo(odontologos::class);
    }
    
    public function clinica()
    {
        return $this->belongsTo(Clinica::class);
    }

    protected $fillable = [
        'id_paciente', 

        'id_creacion',
        'id_modificacion',

        'id_odontologo',
        'id_clinica',
        'consulta',
        
        'box18',
        'box17',
        'box16',
        'box15',
        'box14',
        'box13',
        'box12',
        'box11',
        'box21',
        'box22',
        'box23',
        'box24',
        'box25',
        'box26',
        'box27',
        'box28',
        'box48',
        'box47',
        'box46',
        'box45',
        'box44',
        'box43',
        'box42',
        'box41',
        'box48',
        'box47',
        'box46',
        'box45',
        'box44',
        'box43',
        'box42',
        'box41',
        'box31',
        'box32',
        'box33',
        'box34',
        'box35',
        'box36',
        'box37',
        'box38',

        'siConGuias',
        'noConGuias',

        'listaServicios',
        'impresionServicios',
        'arryServicios',
        'listaItems',
        'metodoPago',
        'precio_final',

        'otrosAnalisis',
        'estado',
        'activeComision',

    ];
}

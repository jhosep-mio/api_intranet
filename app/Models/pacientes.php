<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class pacientes extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = "pacientes";
    protected $fillable = [
        'id_rol', 
        'nombres',
        'apellido_p',
        'apellido_m',
        'f_nacimiento',
        'nombre_apoderado',
        'tipo_documento_apoderado',
        'documento_apoderado',
        'tipo_documento_paciente_odontologo',
        'numero_documento_paciente_odontologo',
        'celular',
        'correo',
        'genero',
        'embarazada',
        'enfermedades',
        'discapacidades',
        'paciente_especial'];
}

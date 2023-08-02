<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class odontologos extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = "odontologos";

    public function clinica()
    {
        return $this->belongsTo(Clinica::class);
    }

    public function ordenes()
    {
        return $this->hasMany(ordenes::class);
    }

    protected $fillable = [
        'id_rol', 
        'clinica', 
        'cop', 
        'c_bancaria', 
        'cci',
        'nombre_banco', 
        'nombres',
        'apellido_p',
        'apellido_m',
        'f_nacimiento',
        'tipo_documento_paciente_odontologo',
        'numero_documento_paciente_odontologo',
        'celular',
        'correo',
        'genero',
    ];

}

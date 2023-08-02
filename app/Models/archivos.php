<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class archivos extends Model
{
    use HasFactory;
    protected $fillable = [
       'id_orden',
       'id_servicio',
       'archivo',
    ];
}

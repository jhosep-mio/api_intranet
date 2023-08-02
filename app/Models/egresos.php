<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class egresos extends Model
{
    use HasFactory;
    protected $fillable = [
        'descripcion',
        'cantidad',
        'total',
     ]; 
}

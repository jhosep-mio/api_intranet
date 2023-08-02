<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class detalle_ordenes extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_orden', 
        'detalle_items', 
    ];
}

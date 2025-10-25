<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeticionAPI extends Model
{
    use HasFactory;

    protected $table="peticiones_api";
    protected $fillable = [
        'exitosas_nombres',
        'exitosas_puestos',
        'progreso',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];



}

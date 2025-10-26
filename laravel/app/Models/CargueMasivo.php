<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargueMasivo extends Model
{
    use HasFactory;

    protected $table="carguesmasivos";
    protected $fillable = [
        'total',
        'errores',
        'exitosos',
        'usuario'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];


}

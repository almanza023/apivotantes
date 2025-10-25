<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResultadoAPI extends Model
{
    use HasFactory;

    protected $table="resultado_api";
    protected $fillable = [
        'numerodocumento',
        'nombre_expe',
        'fecha_expe',
        'estado_expe',
        'lugar_expe',
        'departamento',
        'municipio',
        'mesa',
        'puesto',
        'direccion',
        'estado',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public static function validarDocumento($numero){
        return Votante::where('numerodocumento', $numero)
        ->first();
    }



}

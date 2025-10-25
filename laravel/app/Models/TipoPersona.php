<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoPersona extends Model
{
    use HasFactory;

    protected $table="tipos_personas";
    protected $fillable = [
        'descripcion',
        'estado',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];


    public function setDescripcionAttribute($value)
    {
        $this->attributes['descripcion'] = strtoupper(($value));
    }

    public static function getActive(){
        return TipoPersona::where('estado', 1)->orderBy('id', 'asc')->get();
    }

}

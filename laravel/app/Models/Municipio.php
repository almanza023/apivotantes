<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
    use HasFactory;

    protected $table="municipios";
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
        return Municipio::where('estado', 1)->orderBy('descripcion', 'asc')->get();
    }

}

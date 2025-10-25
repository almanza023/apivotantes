<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PuestoVotacion extends Model
{
    use HasFactory;

    protected $table="puesto_votacion";
    protected $fillable = [
        'municipio_id',
        'user_id',
        'descripcion',
        'coordinador',
        'estado',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function municipio()
    {
      return $this->belongsTo(Municipio::class);
    }

    public function user()
    {
      return $this->belongsTo(User::class);
    }

    public function setDescripcionAttribute($value)
    {
        $this->attributes['descripcion'] = strtoupper(($value));
    }

    public static function getActive(){
        return PuestoVotacion::where('estado', 1)->orderBy('descripcion', 'asc')->get();
    }


}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campanna extends Model
{
    use HasFactory;

    protected $table="campannas";
    protected $fillable = [
        'municipio_id',
        'user_id',
        'descripcion',
        'corporacion',
        'partido',
        'logo',
        'api',
        'peticionesapi',
        'estado',
    ];

    public function barrio()
    {
      return $this->belongsTo(Barrio::class);
    }

    public function municipio()
    {
      return $this->belongsTo(Municipio::class);
    }

    public function user()
    {
      return $this->belongsTo(User::class);
    }

    public function tipo_persona()
    {
      return $this->belongsTo(TipoPersona::class);
    }

    public function lider()
    {
      return $this->belongsTo(Persona::class, 'lider_id', 'id');
    }

    public function sublider()
    {
      return $this->belongsTo(Persona::class, 'sublider_id', 'id');
    }

    public function puesto()
    {
      return $this->belongsTo(PuestoVotacion::class, 'puesto_id', 'id');
    }


    public function setDescripcionAttribute($value)
    {
        $this->attributes['descripcion'] = strtoupper(($value));
    }

    public function setPartidoAttribute($value)
    {
        $this->attributes['partido'] = strtoupper(($value));
    }

    public function setCorporacionttribute($value)
    {
        $this->attributes['corporacion'] = strtoupper(($value));
    }

    public static function getActive(){
        return Campanna::where('estado', 1)->orderBy('descripcion', 'asc')->get();
    }

    public static function validarDocumento($numero){
        return Votante::where('numerodocumento', $numero)
        ->with('lider')
        ->with('sublider')
        ->with('puesto')
        ->with('user')
        ->first();
    }

}

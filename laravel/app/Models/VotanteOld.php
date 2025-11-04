<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VotanteOld extends Model
{
    use HasFactory;

    protected $table="votantes_old";
    protected $fillable = [
        'tipo_persona_id',
        'barrio_id',
        'municipio_id',
        'user_id',
        'puesto_id',
        'lider_id',
        'sublider_id',
        'numerodocumento',
        'nombrecompleto',
        'fecha_expedicion',
        'telefono',
        'mesa',
        'estado',
        'departamento',
        'municipio',
        'puestovotacion',
        'direccion',
        'mesavotacion',
        'fechapuesto',
        'usuariosube',
        'confirmado',
        'fechaconfirmado',
        'ip',
        'puestoconfirmado',
        'motivollamada',
        'apiname',
        'fechaapiname',
        'apipuesto',
        'fechaapipuesto',
        'usuariocreacion',
        'idcarguemasivo',
        'usuarioactualiza',
        'fechaactualiza'
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


    public function setNombreCompletoAttribute($value)
    {
        $this->attributes['nombrecompleto'] = strtoupper(($value));
    }

    public static function getActive(){
        return Votante::where('estado', 1)->orderBy('nombrecompleto', 'asc')->get();
    }

    public static function validarDocumento($numero){
        return Votante::where('numerodocumento', $numero)
        ->with('lider')
        ->with('sublider')
        ->with('puesto')
        ->with('user')
        ->first();
    }

    public static function getById($id){
        return Votante::where('id', $id)
        ->with('lider')
        ->with('sublider')
        ->with('puesto')
        ->with('user')
        ->first();
    }

    public static function getByLider($lider, $sublider){
        if(empty($sublider)){
            return Votante::where('lider_id', $lider)
            ->whereNull('sublider_id')
            ->with('lider')
            ->with('puesto')
            ->with('barrio');
        }else{
            return Votante::where('lider_id', $lider)
            ->where('sublider_id', $sublider)
            ->with('lider')
            ->with('puesto')
            ->with('barrio');
        }
    }

    public static function getFiltros(){

    }

    public static function cantidadVotos($lider, $sublider){
        if(empty($sublider)){
            return Votante::where('lider_id', $lider)->count();
        }else{
            return Votante::where('lider_id', $lider)
            ->where('sublider_id', $sublider)
            ->count();
        }
    }

    public static function transferirVotantes($lider, $sublider, $lider_mov){
       if(empty($sublider)){
        return DB::update('update votantes set lider_id = ? where lider_id=?',  [$lider_mov ,
        $lider]);
       }else{
        return DB::update('update votantes set lider_id = ? where lider_id=? and sublider_id=?',  [$lider_mov ,
        $lider, $sublider]);
       }
    }

    public static function getVotantesPuesto($fecha1, $fecha2){
        return DB::select("select id, numerodocumento  from votantes v where puestovotacion is null and cast(created_at as date)>= ? and cast(created_at as date) <= ? and estado=1 limit 1", [$fecha1, $fecha2]);
     }

     public static function getTotalPuestoVotacion(){
        return DB::select("select v.puestovotacion, v.direccion, count(*) as totalvotos
        from votantes v where v.municipio='SINCELEJO'
        group by v.puestovotacion, v.direccion
        order by CONVERT(count(*), SIGNED) desc");
     }

     public static function getTotalMesaVotacion($puesto){
        return DB::select("select v.mesavotacion, count(*) as totalvotos
        from votantes v where v.puestovotacion =?
        group by v.mesavotacion
        order by CONVERT(count(*), SIGNED) desc", [$puesto]);
     }

     public static function getByLiderCodigo($lider, $sublider){
        if(empty($sublider)){
            return DB::select("SELECT *
            FROM votantes v
            where v.lider_id=? and v.sublider_id is null
            ORDER BY
              CASE
                WHEN municipio  = 'SINCELEJO' THEN 1
                ELSE 2
              END, municipio;", [$lider]);
        }else{
            return DB::select("SELECT *
            FROM votantes v
            where v.lider_id=? and v.sublider_id=?
            ORDER BY
              CASE
                WHEN municipio  = 'SINCELEJO' THEN 1
                ELSE 2
              END, municipio;", [$lider, $sublider]);
        }
    }

    public static function getByLiderTicket($lider, $sublider){
        if(empty($sublider)){
            return DB::select("SELECT id
            FROM votantes v
            where v.lider_id=? and v.sublider_id is null and municipio='SINCELEJO'", [$lider]);
        }else{
            return DB::select("SELECT id
            FROM votantes v
            where v.lider_id=? and v.sublider_id=?
            and municipio='SINCELEJO' ", [$lider, $sublider]);
        }
    }



}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Persona extends Model
{
    use HasFactory;

    protected $table="personas";
    protected $fillable = [
        'tipo_persona_id',
        'barrio_id',
        'lider_id',
        'numerodocumento',
        'nombrecompleto',
        'telefono',
        'estado',
        'deparatemento',
        'municipio',
        'municipio_id',
        'puestovotacion',
        'mesavotacion',
        'fechapuesto',
        'usuariosube'
    ];

    public function setNombreCompletoAttribute($value)
    {
        $this->attributes['nombrecompleto'] = strtoupper(($value));
    }

    public function barrio()
    {
      return $this->belongsTo(Barrio::class);
    }

    public function tipo_persona()
    {
      return $this->belongsTo(TipoPersona::class);
    }

    public function municipio()
    {
      return $this->belongsTo(related: Municipio::class);
    }

    public function lider()
    {
      return $this->belongsTo(Persona::class);
    }

    public static function getActive(){
        return Persona::where('estado', 1)->orderBy('nombrecompleto', 'asc')->get();
    }

    public static function getAll(){
        return Persona::where('tipo_persona_id', '1')->
         with('barrio')
        ->with('lider')
        ->with('tipo_persona')->get();
    }

    public static function getLideresySublider($id, $municipio_id){
        $query = Persona::where('tipo_persona_id', $id)->with('barrio');
        if($municipio_id != 0){
            $query = $query->where('municipio_id', $municipio_id);
        }
        return $query->orderBy('nombrecompleto', 'asc')->get();
    }

    public static function getSublideres($id){
        return Persona::where('lider_id', $id)->
        where('tipo_persona_id', 2)->
        orderBy('nombrecompleto', 'asc')->get();
    }

    public static function getLider($id){
        return Persona::where('id', $id)->
        where('tipo_persona_id', 1)->
        orderBy('nombrecompleto', 'asc')->first();
    }


    public static function getSublider($id){
        return Persona::where('id', $id)->
        where('tipo_persona_id', 2)->
        orderBy('nombrecompleto', 'asc')->get();
    }

    public static function getDatosSublider($id){
        return Persona::where('id', $id)->
        where('tipo_persona_id', 2)->
        orderBy('nombrecompleto', 'asc')->first();
    }

    public static function validarDuplicado($documento){
        return Persona::where('numerodocumento', $documento)->first();
    }

    public static function getDataLideres($municipio_id){
        $where = "p.tipo_persona_id='1'";
        if($municipio_id != 0){
            $where .= " and p.municipio_id=$municipio_id";
        }
        return DB::select("SELECT p.id, p.nombrecompleto, p.numerodocumento, p.telefono,
        b.descripcion as barrio, t.descripcion as tipo, p.estado, m.descripcion as municipio, m.id as municipio_id,
        (SELECT COUNT(*)  FROM personas WHERE tipo_persona_id=2 and lider_id=p.id) AS totalsublideres,
        (SELECT COUNT(*)  FROM votantes WHERE lider_id=p.id) AS totalvotos,
        (SELECT COUNT(*)  FROM votantes WHERE lider_id=p.id and confirmado='SI') AS confirmados
        FROM personas p
        INNER JOIN barrios b ON (p.barrio_id=b.id)
        INNER JOIN tipos_personas t ON (p.tipo_persona_id=t.id)
        INNER JOIN municipios m ON (p.municipio_id=m.id)
        WHERE $where
        ORDER BY p.id DESC");
    }

    public static function getDataSubLideres($id){
        return DB::select("SELECT p.id, p.nombrecompleto, p.numerodocumento, p.telefono,
        b.descripcion as barrio, p.estado, p.tipo_persona_id as tipo_id,
        (SELECT COUNT(*)  FROM votantes WHERE sublider_id=p.id) AS totalvotos
        FROM personas p
        INNER JOIN barrios b ON (p.barrio_id=b.id)
        WHERE p.tipo_persona_id=2 and p.lider_id=$id
        ORDER BY p.id DESC");
    }

    public static function getEstadisticas($documento){
        return DB::select("SELECT p.id, p.nombrecompleto, p.numerodocumento, p.telefono,
        b.descripcion as barrio, t.descripcion as tipo, p.estado,
        (SELECT COUNT(*)  FROM votantes WHERE sublider_id=p.id) AS totalvotos,
        (SELECT nombrecompleto  FROM personas WHERE id=p.lider_id) AS lider
        FROM personas p
        INNER JOIN barrios b ON (p.barrio_id=b.id)
        INNER JOIN tipos_personas t ON (p.tipo_persona_id=t.id)
        WHERE p.numerodocumento=$documento
        ORDER BY p.id DESC limit 1");
    }

    public static function getVotantes($id, $tipo){
        if($tipo==1){
                return Votante::where('lider_id', $id)
                ->with('lider')
                ->with('sublider')
                ->with('puesto')
                ->with('barrio')
                ->with('user')
                ->get();
        }else if($tipo==2){
            return Votante::where('sublider_id', $id)
            ->with('lider')
            ->with('sublider')
            ->with('puesto')
            ->with('barrio')
            ->with('user')
            ->get();
        }else{
            return[];
        }
        }

    public static function getEstadisticasLider(){
        return DB::select("SELECT p.id, p.nombrecompleto, p.telefono, p.numerodocumento,
        (SELECT COUNT(*) FROM votantes v WHERE v.lider_id=p.id ) AS totalvotos,
        (SELECT COUNT(*) FROM personas v WHERE v.lider_id=p.id ) AS totalsublider
         FROM personas p
         WHERE p.tipo_persona_id=1");
    }

    public static function getEstadisticasSublider($id){
        return DB::select("SELECT p.id, p.nombrecompleto, p.telefono, p.numerodocumento,
        (SELECT COUNT(*) FROM votantes v WHERE v.sublider_id=p.id ) AS totalvotos
         FROM personas p
         WHERE p.tipo_persona_id=2 and p.lider_id='$id'");
    }

    public static function getLideres(){
        return Persona::where('tipo_persona_id', 1)->
        where('estado', 1)
        ->orderBy('nombrecompleto', 'asc')->get();
    }

    public static function getPersonasPuesto($fecha1, $fecha2){
        return DB::select("select id, numerodocumento  from personas v where puestovotacion is null and cast(created_at as date)>= ? and cast(created_at as date) <= ? and estado=1 limit 1", [$fecha1, $fecha2]);
     }

     public static function getSublideresCodigo($id){
        return DB::select("SELECT *
        FROM personas v
        where v.lider_id=? and v.tipo_persona_id=2
        ORDER BY
          CASE
            WHEN municipio  = 'SINCELEJO' THEN 1
            ELSE 2
          END, municipio", [$id]);
    }

    public static function getSublideresTicket($id){
        return DB::select("SELECT id
        FROM personas v
        where v.lider_id=? and v.tipo_persona_id=2
        and v.municipio='SINCELEJO' ", [$id]);
    }



}











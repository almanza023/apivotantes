<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FuenteData extends Model
{
    use HasFactory;

    protected $table="fuente_data";
    protected $fillable = [
        'tipodocumento',
        'numerodocumento',
        'papellido',
        'sapellido',
        'pnombre',
        'snombre',
        'nombrecompleto',
        'lider',
        'estado',
    ];

    public function setPApellidoCompletoAttribute($value)
    {
        $this->attributes['papellido'] = strtoupper(($value));
    }
    public function setSApellidoCompletoAttribute($value)
    {
        $this->attributes['sapellido'] = strtoupper(($value));
    }
    public function setPNombreCompletoAttribute($value)
    {
        $this->attributes['pnombre'] = strtoupper(($value));
    }
    public function setSNombreCompletoAttribute($value)
    {
        $this->attributes['snombre'] = strtoupper(($value));
    }




    public static function getByDocumento($numero){
        return FuenteData::where('numerodocumento', $numero)->first();

    }

    public static function getErrores($fecha, $fecha2, $lider){
        if($lider=="Todos"){
           return DB::select("SELECT numerodocumento FROM
        fuente_data f WHERE (f.nombrecompleto IS NULL OR f.nombrecompleto='')
        AND CAST(f.created_at AS DATE)>='$fecha' AND CAST(f.created_at AS DATE)<='$fecha2' "); 
        }else{
            return DB::select("SELECT numerodocumento FROM
        fuente_data f WHERE (f.nombrecompleto IS NULL OR f.nombrecompleto='')
        AND CAST(f.created_at AS DATE)>='$fecha' AND CAST(f.created_at AS DATE)<='$fecha2' and f.lider='$lider'");
        }
    }
    public static function getValidos($fecha, $fecha2, $lider){
        if($lider=="Todos"){
           return DB::select("SELECT numerodocumento FROM
        fuente_data f WHERE (f.nombrecompleto<>'')
        AND CAST(f.created_at AS DATE)>='$fecha' AND CAST(f.created_at AS DATE)<='$fecha2'  "); 
        }else{
            return DB::select("SELECT numerodocumento FROM
        fuente_data f WHERE (f.nombrecompleto<>'')
        AND CAST(f.created_at AS DATE)>='$fecha' AND CAST(f.created_at AS DATE)<='$fecha2' and f.lider='$lider' ");
        }
    }





}

<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Campanna;
use App\Models\Municipio;
use App\Models\Persona;
use App\Models\Votante;
use Illuminate\Http\Request;
use JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class EstadisticaController extends Controller
{
    protected $user;
    protected $model;

    public function __construct(Request $request)
    {
        $token = $request->header('Authorization');

        if($token != '')
            //En caso de que requiera autentifiación la ruta obtenemos el usuario y lo almacenamos en una variable, nosotros no lo utilizaremos.
            $this->user = JWTAuth::parseToken()->authenticate();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //Listamos todos los productos
    $votos=count(Votante::where('estado', 1)->get());
    $lideres=count(Persona::where('tipo_persona_id', 1)->get());
    $sublideres=count(Persona::where('tipo_persona_id', 2)->get());
    $votos=count(Votante::get());
    $potencialelectoral=$votos+$lideres+$sublideres;
    $confirmados=count(Votante::where('confirmado', 'SI')->get());
    $porcentajeConfirmado=round(($confirmados*100)/$potencialelectoral,2);


    $votantesSincelejo=Votante::where("municipio", "SINCELEJO")->count();
    $votantesOtrasCiudad=Votante::where("municipio", "<>", "SINCELEJO")->count();

    $lideresSincelejo=Persona::where("municipio", "SINCELEJO")->count();
    $lideresOtrasCiudad=Persona::where("municipio", "SINCELEJO")->count();

    $potencialSincelejo=$votantesSincelejo+ $lideresSincelejo;
    $potencialOtraCiudad=$votantesOtrasCiudad+ $lideresOtrasCiudad;

    $municipios=count(Municipio::getActive());

    $objeto=[
        'votantes'=>$potencialelectoral,
        'lideres'=>$lideres,
        'sublideres'=>$sublideres,
        'confirmados'=>$confirmados,
        'porcentajeConfirmado'=>$porcentajeConfirmado,
        'potencialSincelejo'=>$potencialSincelejo,
        'potencialOtraCiudad'=>$potencialOtraCiudad,
        'municipios'=>$municipios,
    ];
       if($objeto){
        return response()->json([
            'code'=>200,
            'data' => $objeto
        ], Response::HTTP_OK);
       }else{
        return response()->json([
            'code'=>200,
            'data' => []
        ], Response::HTTP_OK);
       }

    }

    public function getEstadisticasLideres()
    {
        //Listamos todos los productos
      $objeto=Persona::getEstadisticasLider();
       if($objeto){
        return response()->json([
            'code'=>200,
            'data' => $objeto
        ], Response::HTTP_OK);
       }else{
        return response()->json([
            'code'=>200,
            'data' => []
        ], Response::HTTP_OK);
       }

    }

   public function getDigitados($usuario)
    {
        //Listamos todos los productos
      $objeto=DB::select("select count(*) from votantes v where
      v.puestovotacion is not null and v.usuariosube='ealmanza'
      and v.estado=3", [$usuario]);
       if($objeto){
        return response()->json([
            'code'=>200,
            'data' => $objeto
        ], Response::HTTP_OK);
       }else{
        return response()->json([
            'code'=>200,
            'data' => []
        ], Response::HTTP_OK);
       }

    }

    public function getEstadisticasAPI(Request $request)
    {
        //Listamos todos los productos
        $fechaInicial=$request->fechainicio;
        $fechaFinal=$request->fechafinal;
       $votantes=Votante::where('apiname', true)
       ->orWhere('apipuesto', true)
       ->where(DB::raw('DATE(created_at)'), '>=', DB::raw("DATE('$fechaInicial')"))
       ->where(DB::raw('DATE(created_at)'), '<=', DB::raw("DATE('$fechaFinal')"))
       ->count();

       $responsables=Persona::where(DB::raw('DATE(created_at)'), '>=', DB::raw("DATE('$fechaInicial')"))
       ->where(DB::raw('DATE(created_at)'), '<=', DB::raw("DATE('$fechaFinal')"))
       ->count();

       $total = $votantes + $responsables;
       $data=[
        'votantes' => $votantes,
        'responsables' => $responsables,
        'total' => $total
       ];

       if($votantes){
        return response()->json([
            'code'=>200,
            'data' => $data
        ], Response::HTTP_OK);
       }else{
        return response()->json([
            'code'=>200,
            'data' => $data
        ], Response::HTTP_OK);
       }

    }







}

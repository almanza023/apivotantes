<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Barrio;
use App\Models\Votante;
use Carbon\Carbon;
use Illuminate\Http\Request;
use JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class ApiRespuestaController extends Controller
{
    protected $user;
    protected $model;

    public function __construct(Request $request)
    {
        // $token = $request->header('Authorization');
        // $this->model=Barrio::class;
        // if($token != ''){
        //      //En caso de que requiera autentifiación la ruta obtenemos el usuario y lo almacenamos en una variable, nosotros no lo utilizaremos.
        //     $this->user = JWTAuth::parseToken()->authenticate();
        // }

    }

    public function actualizarRespuestAPI(Request $request){
        $documento=$request->numerodocumento;
        $nombrecompleto=$request->nombrecompleto;
        $departamento=$request->departamento;
        $municipio=$request->municipio;
        $puesto=$request->puesto;
        $mesa=$request->mesa;
        $direccion=$request->direccion;

        $votante=Votante::where('numerodocumento', $documento)->first();
        if($votante && empty($votante->nombrecompleto)){
            $votante->update([
                'nombrecompleto' => $nombrecompleto,
                'apiname' => true,
                'fechaapiname'=>Carbon::now()->format('Y-m-d H:i:s')
            ]);
            if(!empty($departamento)){
                $votante->update([
                    'departamento' => $departamento,
                    'municipio' => $municipio,
                    'puestovotacion' => $puesto,
                    'mesavotacion' => $mesa,
                    'direccion' => $direccion,
                    'apipuesto' => true,
                    'fechaapipuesto'=>Carbon::now()->format('Y-m-d H:i:s')
                ]);
            }

            return response()->json([
            'status'=>'ok',
            'message' => 'Operación Finalizada Exitosamente',
        ], Response::HTTP_OK);
        }else{
            return response()->json([
            'status'=>'error',
            'message' => 'No se actualizó',
        ], Response::HTTP_OK);
        }
    }

    public function actualizarPuestoRespuestAPI(Request $request){
        $documento=$request->numerodocumento;
        $departamento=$request->departamento;
        $municipio=$request->municipio;
        $puesto=$request->puesto;
        $mesa=$request->mesa;
        $direccion=$request->direccion;

        $votante=Votante::where('numerodocumento', $documento)->first();
        if($votante){
            if(!empty($departamento)){
                $votante->update([
                    'departamento' => $departamento,
                    'municipio' => $municipio,
                    'puestovotacion' => $puesto,
                    'mesavotacion' => $mesa,
                    'direccion' => $direccion,
                    'apipuesto' => true,
                    'fechaapipuesto'=>Carbon::now()->format('Y-m-d H:i:s')
                ]);
            }
            return response()->json([
            'status'=>'ok',
            'message' => 'Operación Finalizada Exitosamente',
        ], Response::HTTP_OK);
        }else{
            return response()->json([
            'status'=>'error',
            'message' => 'No se actualizó',
        ], Response::HTTP_OK);
        }
    }


}

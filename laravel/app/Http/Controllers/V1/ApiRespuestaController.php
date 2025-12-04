<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Barrio;
use App\Models\LogAPI;
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

    public function actualizarRespuestAPI(Request $request)
    {
        $documento = $request->numerodocumento;
        $nombrecompleto = $request->nombrecompleto;
        $departamento = $request->departamento ?? null;
        $municipio = $request->municipio ?? null;
        $puesto = $request->puesto ?? null;
        $mesa = $request->mesa ?? null;
        $direccion = $request->direccion ?? null;

        $votante = Votante::where('numerodocumento', $documento)->with('municipioResidencia')->first();
        if ($votante) {

            $votante->update([
                'nombrecompleto' => $nombrecompleto,
                'apiname' => true,
                'fechaapiname' => Carbon::now()->format('Y-m-d H:i:s')
            ]);

            if ($departamento) {

                //Verifica si el municipio es el mismo donde se registro
                if ($votante->municipioResidencia->descripcion != $municipio) {
                    $votante->update([
                        'mismoMunicipio' => 1
                    ]);
                } else {
                    $votante->update([
                        'mismoMunicipio' => 0
                    ]);
                }

                $votante->update([
                    'departamento' => $departamento,
                    'municipio' => $municipio,
                    'puestovotacion' => $puesto,
                    'mesavotacion' => $mesa,
                    'direccion' => $direccion,
                    'apipuesto' => true,
                    'fechaapipuesto' => Carbon::now()->format('Y-m-d H:i:s')
                ]);

                $log = new LogAPI();
                $log->create([
                    'respuesta' => "Consulta exitosa API",
                    'operacion' => $votante->numerodocumento,
                ]);
            }

            return response()->json([
                'status' => 'ok',
                'message' => 'Operación Finalizada Exitosamente',
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'No se actualizó',
            ], Response::HTTP_OK);
        }
    }

    public function actualizarPuestoRespuestAPI(Request $request)
    {
        $documento = $request->numerodocumento;
        $departamento = $request->departamento;
        $municipio = $request->municipio;
        $puesto = $request->puesto;
        $mesa = $request->mesa;
        $direccion = $request->direccion;

        $votante = Votante::where('numerodocumento', $documento)->with('municipioResidencia')->first();
        if ($votante) {
            if (!empty($departamento)) {

                //Verifica si el municipio es el mismo donde se registro
                if ($votante->municipioResidencia->descripcion != $municipio) {
                    $votante->update([
                        'mismoMunicipio' => 1
                    ]);
                } else {
                    $votante->update([
                        'mismoMunicipio' => 0
                    ]);
                }

                $votante->update([
                    'departamento' => $departamento,
                    'municipio' => $municipio,
                    'puestovotacion' => $puesto,
                    'mesavotacion' => $mesa,
                    'direccion' => $direccion,
                    'apipuesto' => true,
                    'fechaapipuesto' => Carbon::now()->format('Y-m-d H:i:s')
                ]);

                $log = new LogAPI();
                $log->create([
                    'respuesta' => "Consulta exitosa API",
                    'operacion' => $votante->numerodocumento,
                ]);
            }
            return response()->json([
                'status' => 'ok',
                'message' => 'Operación Finalizada Exitosamente',
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'No se actualizó',
            ], Response::HTTP_OK);
        }
    }
}

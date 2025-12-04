<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\CargueMasivo;
use App\Models\LogPeticion;
use App\Models\Votante;
use App\Models\Persona;
use App\Models\VotanteOld;
use Illuminate\Http\Request;
use JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class VotanteController extends Controller
{
    protected $user;
    protected $model;

    public function __construct(Request $request)
    {
        $token = $request->header('Authorization');
        $this->model = Votante::class;
        if ($token != '') {
            //En caso de que requiera autentifiación la ruta obtenemos el usuario y lo almacenamos en una variable, nosotros no lo utilizaremos.
            $this->user = JWTAuth::parseToken()->authenticate();
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //Listamos todos los productos
        $objeto = $this->model::get();
        if ($objeto) {
            return response()->json([
                'code' => 200,
                'data' => $objeto
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'code' => 200,
                'data' => []
            ], Response::HTTP_OK);
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Validamos los datos
        $data = $request->only(
            'tipo_persona',
            'municipio_id',
            'municipio',
            'user',
            'lider',
            'sublider',
            'numerodocumento',
            'nombrecompleto',
            'telefono',
            'puesto_id',
            'mesa_id',
            'departamento',
            'muncipio',
            'puestovotacion',
            'mesavotacion',
            'direccion'
        );

        $validator = Validator::make($data, [
            'user' => 'required|numeric',
            'numerodocumento' => 'required|numeric',
            'nombrecompleto' => 'required|max:200|string',
            'telefono' => 'required|numeric|min:9',
        ]);


        //Si falla la validación
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }


        //Creamos el producto en la BD
        $objeto = $this->model::updateOrCreate(
            ['numerodocumento' => $request->numerodocumento],
            [
                'municipio_id' => $request->municipio_id,
                'user_id' => $request->user,
                'lider_id' => $request->lider,
                'sublider_id' => $request->sublider,
                'nombrecompleto' => $request->nombrecompleto,
                'telefono' => $request->telefono,
                'departamento' => $request->departamento,
                'municipio' => $request->municipio,
                'puestovotacion' => $request->puestovotacion,
                'mesavotacion' => $request->mesavotacion,
                'direccion' => $request->direccion,
                'estado' => 1,
                'apiname' => true,
                'apipuesto' => true,
            ]
        );

        //Respuesta en caso de que todo vaya bien.
        return response()->json([
            'code' => 200,
            'message' => 'Registro Agreado Exitosamente',
            'data' => $objeto
        ], Response::HTTP_OK);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show($id)

    {
        //Bucamos el producto
        $objeto = $this->model::getById($id);

        //Si el producto no existe devolvemos error no encontrado
        if (!$objeto) {
            return response()->json([
                'code' => 200,
                'message' => 'Registro no encontrado en la base de datos.'
            ], 404);
        }

        //Si hay producto lo devolvemos
        return response()->json([
            'code' => 200,
            'data' => $objeto
        ], Response::HTTP_OK);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //Validación de datos
        $data = $request->only(
            'tipo_persona',
            'barrio',
            'municipio',
            'user',
            'lider',
            'sublider',
            'numerodocumento',
            'nombrecompleto',
            'fecha_expedicion',
            'telefono',
            'puesto',
            'mesa',
            'lidernuevo',
            'sublidernuevo',
            'motivollamada'
        );
        $validator = Validator::make($data, [
            'user' => 'required|numeric',
            'numerodocumento' => 'required|numeric|min:6',
            'nombrecompleto' => 'required|max:200|string',
            'telefono' => 'required|numeric|min:9',
        ]);

        //Si falla la validación error.
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        //Buscamos el producto
        $objeto = $this->model::findOrfail($id);

        //Actualizamos el producto.
        if (!empty($request->lidernuevo)) {
            $objeto->update([
                'lider_id' => $request->lidernuevo,
                'sublider_id' => $request->sublidernuevo,
                'numerodocumento' => $request->numerodocumento,
                'nombrecompleto' => $request->nombrecompleto,
                'telefono' => $request->telefono,
                'motivollamada' => $request->motivollamada,
                'usuarioactualiza' => $this->user->username,
                'fechaactualiza' => Carbon::now()->format('Y-m-d H:i:s')
            ]);
        } else {
            $objeto->update([
                'numerodocumento' => $request->numerodocumento,
                'nombrecompleto' => $request->nombrecompleto,
                'telefono' => $request->telefono,
                'motivollamada' => $request->motivollamada,
                'usuarioactualiza' => $this->user->username,
                'fechaactualiza' => Carbon::now()->format('Y-m-d H:i:s')
            ]);
        }

        //Devolvemos los datos actualizados.
        return response()->json([
            'code' => 200,
            'message' => 'Registro Actualizado Exitosamente',
            'data' => $objeto
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //Buscamos el producto
        $objeto = $this->model::findOrfail($id);

        //Eliminamos el producto
        $objeto->delete();

        //Devolvemos la respuesta
        return response()->json([
            'code' => 200,
            'message' => 'Registro Eliminado'
        ], Response::HTTP_OK);
    }

    public function cambiarEstado(Request $request)
    {
        //Validación de datos
        $data = $request->only('id');
        $validator = Validator::make($data, [
            'id' => 'required'
        ]);
        //Si falla la validación error.
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }
        $objeto = $this->model::findOrfail($request->id);
        if ($objeto->estado == 1) {
            $objeto->estado = 2;
            $objeto->save();
        } else {
            $objeto->estado = 1;
            $objeto->save();
        }
        //Devolvemos los datos actualizados.
        return response()->json([
            'code' => 200,
            'message' => 'Estado Actualizado Extiosamente',
            'data' => $objeto
        ], Response::HTTP_OK);
    }


    public function validarDocumento($documento)
    {
        //Listamos todos los productos

        $objeto = $this->model::validarDocumento($documento);
        if ($objeto) {
            return response()->json([
                'code' => 200,
                'data' => $objeto,
                'duplicado' => 'si'
            ], Response::HTTP_OK);
        } else {

            $validarLider = Persona::validarDuplicado($documento);
            if (!empty($validarLider)) {
                return response()->json([
                    'code' => 200,
                    'data' => $validarLider,
                    'duplicado' => 'si'
                ], Response::HTTP_OK);
            }
            $numerodocumento = $documento;
            // Retry logic: attempt up to 2 times
            $maxRetries = 2;
            $lastException = null;

            for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                try {
                    $url = env('API_ELECTORAL') . '/consultar-nombres';
                    $response = Http::timeout(60)
                        ->withHeaders([
                            'accept' => 'application/json',
                            'Content-Type' => 'application/json',
                        ])
                        ->post($url, [
                            'nuips' => [$numerodocumento],
                            "enviarapi" => false
                        ]);

                    if ($response->successful()) {
                        $result = $response->json();
                        if (isset($result['results']) && count($result['results']) > 0) {
                            $firstResult = $result['results'][0];
                            $votingPlace = $firstResult['voting_place'] ?? null;
                            LogPeticion::create([
                                'respuesta' => 'Consulta Completa Votante',
                                'operacion' => $numerodocumento,
                            ]);

                            return response()->json([
                                'code' => 200,
                                'message' => 'Persona encontrada',
                                'duplicado' => 'no',
                                'data' => [],
                                'name' => $firstResult['name'] ?? '',
                                'departamento' => $votingPlace['DEPARTAMENTO'] ?? '',
                                'municipio' => $votingPlace['MUNICIPIO'] ?? '',
                                'puesto' => $votingPlace['PUESTO'] ?? '',
                                'direccion' => $votingPlace['DIRECCIÓN'] ?? '',
                                'mesa' => $votingPlace['MESA'] ?? '',
                            ], Response::HTTP_OK);
                        } else {
                            return response()->json([
                                'code' => 200,
                                'message' => 'Persona no encontrada',
                                'duplicado' => 'no',
                            ], Response::HTTP_OK);
                        }
                    }
                    // If status is not 200, continue to retry
                    $lastException = new \Exception('HTTP Status: ' . $response->status());
                } catch (\Illuminate\Http\Client\ConnectionException $e) {
                    $lastException = $e;
                    // Wait before retry (except on last attempt)
                    if ($attempt < $maxRetries) {
                        usleep(500000); // Wait 0.5 seconds before retry
                        continue;
                    }
                } catch (\Exception $e) {
                    $lastException = $e;
                    // Wait before retry (except on last attempt)
                    if ($attempt < $maxRetries) {
                        usleep(500000); // Wait 0.5 seconds before retry
                        continue;
                    }
                }
            }

            // All retries failed, return error based on last exception
            if ($lastException instanceof \Illuminate\Http\Client\ConnectionException) {
                return response()->json([
                    'code' => 503,
                    'message' => 'No se pudo conectar al servicio externo después de ' . $maxRetries . ' intentos: ' . $lastException->getMessage(),
                    'name' => '',
                    'duplicado' => 'no',
                ], Response::HTTP_SERVICE_UNAVAILABLE);
            } else {
                return response()->json([
                    'code' => 400,
                    'message' => 'Error al consultar datos después de ' . $maxRetries . ' intentos: ' . ($lastException ? $lastException->getMessage() : 'Error desconocido'),
                    'name' => '',
                    'duplicado' => 'no',
                ], Response::HTTP_BAD_REQUEST);
            }
        }
    }

    public function filtros(Request $request)
    {

        $objeto = Votante::query()
            ->when($request->lider, fn($query, $lider) => $query->where('lider_id', $lider))
            ->when($request->sublider, fn($query, $sublider) => $query->where('sublider_id', $sublider))
            ->when($request->municipio, fn($query, $municipio) => $query->where('municipio_id', $municipio))
            ->with([
                'lider:id,nombrecompleto',
                'sublider:id,nombrecompleto',
                'municipioResidencia:id,descripcion',
                'puesto:id,descripcion',
                'barrio:id,descripcion'
            ])
            ->select(
                'id',
                'nombrecompleto',
                'numerodocumento',
                'telefono',
                'mesa',
                'departamento',
                'municipio as municipiovotacion',
                'direccion',
                'mesavotacion',
                'puestovotacion',
                'barrio_id',
                'lider_id',
                'sublider_id',
                'puesto_id',
                'municipio_id',
                'created_at',
                'mismoMuncipio'
            )
            ->get();

        if ($objeto) {

            $responseArray = [];
            foreach ($objeto as $item) {
                $tempArray = [
                    'id' => $item->id,
                    'nombrecompleto' => $item->nombrecompleto ?? '',
                    'numerodocumento' => $item->numerodocumento ?? '',
                    'telefono' => $item->telefono ?? '',
                    'puesto' => $item->puesto->descripcion ?? '',
                    'mesa' => $item->mesa ?? '',
                    'barrio' => $item->barrio->descripcion ?? '',
                    'lider' => $item->lider->nombrecompleto ?? '',
                    'municipioresidencia' => $item->municipioResidencia->descripcion ?? '',
                    'fecha_creacion' => $item->created_at ? $item->created_at->format('d M Y - H:i:s') : '',
                    'municipiovotacion' => $item->municipiovotacion,
                    'departamentovotacion' => $item->departamento,
                    'puestovotacion' => $item->puestovotacion,
                    'mesavotacion' => $item->mesavotacion,
                    'direccion' => $item->direccion,
                    'mismoMuncipio' => $item->mismoMuncipio,

                ];
                if ($item->sublider) {
                    $tempArray['sublider'] = $item->sublider->nombrecompleto ?? '';
                }
                array_push($responseArray, $tempArray);
            }
            return response()->json([
                'code' => 200,
                'data' => $responseArray
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'code' => 400,
                'data' => []
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function tranferirVotantes(Request $request)
    {
        $lider = $request->lider;
        $sublider = $request->sublider;
        $lider_mov = $request->lider_mov;
        $resultado = Votante::transferirVotantes($lider, $sublider, $lider_mov);
        if ($resultado) {
            return response()->json([
                'code' => 200,
                'message' => "Votantes Transferidos Exitosamente"
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'code' => 200,
                'message' => "Error al Tranferir Votantes"
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function agregarPuesto(Request $request)
    {
        //Validación de datos
        $data = $request->only('documento', 'departamento', 'municipio', 'puesto', 'mesa', 'direccion', 'usuariosube');
        $validator = Validator::make($data, [
            'documento' => 'required'
        ]);

        //Si falla la validación error.
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        $documento = $request->documento;
        $date = Carbon::now()->format('Y-m-d');
        //Buscamos el producto
        $objeto = $this->model::where('numerodocumento', $documento)->first();

        //Actualizamos el producto.
        $objeto->update([
            'departamento' => $request->departamento,
            'municipio' => $request->municipio,
            'puestovotacion' => $request->puestovotacion,
            'direccion' => $request->direccion,
            'mesavotacion' => $request->mesavotacion,
            'fechapuesto' => $date,
            'estado' => 3,
            'usuariosube' => $request->usuariosube
        ]);
        if ($objeto) {
            //Devolvemos los datos actualizados.
            return response()->json([
                'code' => 200,
                'message' => 'OK',
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'code' => 400,
                'message' => 'ERROR',
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function votantesSinPuesto(Request $request)
    {
        //Listamos todos los productos
        $objeto = $this->model::getVotantesPuesto($request->fecha1, $request->fecha2);

        if ($objeto) {
            return response()->json([
                'code' => 200,
                'data' => $objeto
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'code' => 400,
                'data' => []
            ], Response::HTTP_BAD_REQUEST);
        }
    }
    public function getDigitados($usuario)
    {
        //Listamos todos los productos
        $objeto = DB::select("select count(*) as total from votantes v where v.puestovotacion is not null and v.usuariosube=? and v.estado=3", [$usuario]);
        if ($objeto) {
            return response()->json([
                'code' => 200,
                'data' => $objeto[0]
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'code' => 200,
                'data' => []
            ], Response::HTTP_OK);
        }
    }

    public function confirmarVoto(Request $request)
    {
        //Validación de datos
        $data = $request->only('id', 'ip');
        $validator = Validator::make($data, [
            'id' => 'required'
        ]);

        //Si falla la validación error.
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        $documento = $request->documento;
        $date = Carbon::now()->format('Y-m-d');
        //Buscamos el producto
        $objeto = $this->model::find($request->id);

        //Actualizamos el producto.
        $objeto->update([
            'confirmado' => "SI",
            'ip' => "",
            'fechaconfirmado' => $date,
        ]);
        if ($objeto) {
            //Devolvemos los datos actualizados.
            return response()->json([
                'code' => 200,
                'message' => 'Registro Confirmado',
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'code' => 400,
                'message' => 'ERROR',
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function validarDocumentoConfirmacion($id, $puesto)
    {
        //Listamos todos los productos
        if (strpos($id, "S")) {
            $idnuevo = trim($id, "S");
            $objeto = Persona::validarConfirmacion($idnuevo, $puesto);
        } else {
            $objeto = $this->model::validarConfirmacion($id, $puesto);
        }
        //Si el producto no existe devolvemos error no encontrado
        if (!$objeto) {
            return response()->json([
                'code' => 200,
                'message' => []
            ], 404);
        }

        //Si hay producto lo devolvemos
        return response()->json([
            'code' => 200,
            'data' => $objeto
        ], Response::HTTP_OK);
    }

    public function totalConfirmadosUsuario($usuario)
    {
        //Listamos todos los productos
        //Bucamos el producto
        $objeto = $this->model::getTotalConfirmadoUsuario($usuario);
        return response()->json([
            'code' => 200,
            'data' => $objeto
        ], 200);
    }

    public function votantesSinPuestoFecha(Request $request)
    {
        $documentos = $request->documentos;
    }

    public function cargarVotantes(Request $request)
    {
        $votantes = $request->input('votantes', []);
        $cargados = 0;
        $errores = 0;
        $validados = 0;
        $cargados = count($votantes);
        $arrayErrores = [];
        DB::beginTransaction();
        try {
            if ($cargados > 0) {
                $cargueMasivo = CargueMasivo::create([
                    'total' => $cargados,
                    'errores' => $errores,
                    'exitosos' => $validados,
                    'usuario' => $this->user->username,
                ]);
            }
            foreach ($votantes as $votante) {
                $validar = Votante::with(['lider', 'sublider', 'municipioResidencia'])
                    ->where('numerodocumento', $votante['numerodocumento'])->first();
                if ($validar) {
                    $errores++;
                    array_push($arrayErrores, $validar);
                    continue;
                }
                $votante = Votante::create([
                    'numerodocumento' => $votante['numerodocumento'],
                    'municipio_id' => $votante['municipio_id'],
                    'lider_id' => $votante['lider_id'],
                    'sublider_id' => $votante['sublider_id'],
                    'telefono' => $votante['telefono'],
                    'estado' => 1,
                    'usuariocreacion' => $this->user->username,
                    'idcarguemasivo' => $cargueMasivo->id
                ]);
                //Buscar en VotanteOld
                $votanteOld = VotanteOld::where('numerodocumento', $votante->numerodocumento)->first();
                if ($votanteOld) {
                    $votante->nombrecompleto = $votanteOld->nombrecompleto;
                    $votante->departamento = $votanteOld->departamento;
                    $votante->municipio = $votanteOld->municipio;
                    $votante->puestovotacion = $votanteOld->puestovotacion;
                    $votante->direccion = $votanteOld->direccion;
                    $votante->mesavotacion = $votanteOld->mesavotacion;
                    $votante->fechapuesto = Carbon::now()->format('Y-m-d');
                    $votante->apiname = true;
                    $votante->apipuesto = true;
                    $votante->fechaapiname = Carbon::now()->format('Y-m-d H:i:s');
                    $votante->fechaapipuesto = Carbon::now()->format('Y-m-d H:i:s');
                    $votante->estado = 1;
                    $votante->save();
                }
                $validados++;
            }
            //Actualizar cargue masivo
            $cargueMasivo->errores = $errores;
            $cargueMasivo->exitosos = $validados;
            $cargueMasivo->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'code' => 400,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
        $mensaje = "Cargados: " . $cargados . " Validados: " . $validados . " Errores: " . $errores;
        $data = Votante::where('idcarguemasivo', $cargueMasivo->id)->get();
        return response()->json([
            'code' => 200,
            'message' => $mensaje,
            'carguemasivo' => $cargueMasivo,
            'detailError' => $arrayErrores,
            'data' => $data
        ], Response::HTTP_OK);
    }




    public function actualizarNombreAPI(Request $request)
    {
        $idcarguemasivo = $request->idcarguemasivo;
        $client = new \GuzzleHttp\Client([
            'timeout' => 120,
            'connect_timeout' => 30
        ]);
        $processed = 0;
        $updated = 0;
        $failed = 0;
        $votantes = Votante::where('idcarguemasivo', $idcarguemasivo)
            ->where(function ($query) {
                $query->whereNull('nombrecompleto')
                    ->orWhere('nombrecompleto', '')
                    ->orWhereNull('departamento')
                    ->orWhere('departamento', '');
            })->get();

        $processed = count($votantes);
        $arrayNuips = [];
        $mensaje = 'Operación se esta realizando en Segundo Plano Dar Click en Actualizar para ver el progreso';
        foreach ($votantes as $votante) {
            if (!empty($votante->nombrecompleto) && !empty($votante->departamento)) {
                continue;
            }
            array_push($arrayNuips, $votante->numerodocumento);
        }
        if (count($arrayNuips) > 0) {
            try {
                //obtenner url
                $url = env('API_ELECTORAL') . '/consultar-nombres';
                $response = $client->post($url, [
                    'headers' => [
                        'accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'nuips' => array_values($arrayNuips),
                        'enviarapi' => true
                    ]
                ]);
                if ($response->getStatusCode() != 200) {
                }
                $respuesta = json_decode($response->getBody()->getContents());
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                $failed++;
            }
        } else {
            $mensaje = "No hay votantes para actualizar";
        }
        $votantes = Votante::where('idcarguemasivo', $idcarguemasivo)->get();
        return response()->json([
            'code' => 200,
            'message' => $mensaje,
            'exitosos' => $updated,
            'total' => $processed,
            'errores' => $failed,
            'data' => $votantes
        ], Response::HTTP_OK);
    }

    public function obtenerVotantesByCargue(Request $request)
    {
        $idcarguemasivo = $request->idcarguemasivo;
        $data = Votante::where('idcarguemasivo', $idcarguemasivo)->with('municipioResidencia')->get();
        $carguemasivo = CargueMasivo::where('id', $idcarguemasivo)->first();
        $mensaje = "Cargados: " . $carguemasivo->total . " Validados: " . $carguemasivo->exitosos . " Errores: " . $carguemasivo->errores;

        return response()->json([
            'code' => 200,
            'message' => $mensaje,
            'carguemasivo' => $carguemasivo,
            'data' => $data
        ], Response::HTTP_OK);
    }

    public function consultarDatosPersona(Request $request)
    {
        $numerodocumento = $request->numerodocumento;
        $client = new \GuzzleHttp\Client();
        $votante = Votante::where('numerodocumento', $numerodocumento)->first();
        if (!empty($votante)) {
            return response()->json([
                'code' => 400,
                'message' => 'Persona no encontrada',
                'data' => $votante
            ], Response::HTTP_OK);
        }
        $votante_old = VotanteOld::where('numerodocumento', $numerodocumento)->first();
        if ($votante_old) {
            return response()->json([
                'code' => 400,
                'message' => 'Persona encontrada',
                'name' => $votante_old->nombrecompleto,
                'departamento' => $votante_old->departamento,
                'municipio' => $votante_old->municipio,
                'puesto' => $votante_old->puesto,
                'direccion' => $votante_old->direccion,
                'mesa' => $votante_old->mesa,
            ], Response::HTTP_OK);
        }


        if ($votante) {
            try {
                //obtenner url
                $arrayNuips = [];
                array_push($arrayNuips, $votante->numerodocumento);
                $url = env('API_ELECTORAL') . '/consultar-nombres';
                $response = $client->post($url, [
                    'headers' => [
                        'accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'nuips' => array_values($arrayNuips),
                        'enviarapi' => false
                    ]
                ]);
                if ($response->getStatusCode() != 200) {
                }

                $respuesta = json_decode($response->getBody()->getContents());
                if (isset($respuesta->results) && count($respuesta->results) > 0) {
                    $result = $respuesta->results[0];
                    $votingPlace = $result->voting_place ?? null;

                    return response()->json([
                        'code' => 200,
                        'message' => 'Persona encontrada',
                        'name' => $result->name ?? '',
                        'departamento' => $votingPlace->DEPARTAMENTO ?? '',
                        'municipio' => $votingPlace->MUNICIPIO ?? '',
                        'puesto' => $votingPlace->PUESTO ?? '',
                        'direccion' => $votingPlace->DIRECCIÓN ?? '',
                        'mesa' => $votingPlace->MESA ?? '',
                    ], Response::HTTP_OK);
                }
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                return response()->json([
                    'code' => 400,
                    'message' => 'Error al consultar datos',
                    'data' => $e
                ], Response::HTTP_OK);
            }
        } else {
            $mensaje = "No hay votantes para actualizar";
        }
    }

    public function consultarNombre(Request $request)
    {
        $numerodocumento = $request->numerodocumento;

        // Retry logic: attempt up to 2 times
        $maxRetries = 2;
        $lastException = null;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $url = env('API_ELECTORAL', 'http://localhost:8000') . '/consultar-solo-nombres';
                $response = Http::timeout(60)
                    ->connectTimeout(10)
                    ->withHeaders([
                        'accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ])
                    ->post($url, [
                        'nuip' => $numerodocumento,
                        'fecha_expedicion' => '',
                        'enviarapi' => false
                    ]);

                if ($response->successful()) {
                    $result = $response->json();
                    return response()->json([
                        'code' => 200,
                        'message' => 'Persona encontrada',
                        'name' => $result['name'] ?? '',
                    ], Response::HTTP_OK);
                }

                // If status is not 200, continue to retry
                $lastException = new \Exception('HTTP Status: ' . $response->status());
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                $lastException = $e;
                // Wait before retry (except on last attempt)
                if ($attempt < $maxRetries) {
                    usleep(500000); // Wait 0.5 seconds before retry
                    continue;
                }
            } catch (\Exception $e) {
                $lastException = $e;
                // Wait before retry (except on last attempt)
                if ($attempt < $maxRetries) {
                    usleep(500000); // Wait 0.5 seconds before retry
                    continue;
                }
            }
        }

        // All retries failed, return error based on last exception
        if ($lastException instanceof \Illuminate\Http\Client\ConnectionException) {
            return response()->json([
                'code' => 503,
                'message' => 'No se pudo conectar al servicio externo después de ' . $maxRetries . ' intentos: ' . $lastException->getMessage(),
                'name' => '',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        } else {
            return response()->json([
                'code' => 400,
                'message' => 'Error al consultar datos después de ' . $maxRetries . ' intentos: ' . ($lastException ? $lastException->getMessage() : 'Error desconocido'),
                'name' => '',
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function getDatosPersona(Request $request)
    {
        $numerodocumento = $request->numerodocumento;
        // Retry logic: attempt up to 2 times
        $maxRetries = 2;
        $lastException = null;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $url = env('API_ELECTORAL') . '/consultar-nombres';
                $response = Http::timeout(60)
                    ->withHeaders([
                        'accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ])
                    ->post($url, [
                        'nuips' => [$numerodocumento],
                        "enviarapi" => false
                    ]);

                if ($response->successful()) {
                    $result = $response->json();
                    if (isset($result['results']) && count($result['results']) > 0) {
                        $firstResult = $result['results'][0];
                        $votingPlace = $firstResult['voting_place'] ?? null;
                        LogPeticion::create([
                            'respuesta' => 'Consulta Completa Votante',
                            'operacion' => $numerodocumento,
                        ]);

                        return response()->json([
                            'code' => 200,
                            'message' => 'Persona encontrada',
                            'duplicado' => 'no',
                            'name' => $firstResult['name'] ?? '',
                            'departamento' => $votingPlace['DEPARTAMENTO'] ?? '',
                            'municipio' => $votingPlace['MUNICIPIO'] ?? '',
                            'puesto' => $votingPlace['PUESTO'] ?? '',
                            'direccion' => $votingPlace['DIRECCIÓN'] ?? '',
                            'mesa' => $votingPlace['MESA'] ?? '',
                        ], Response::HTTP_OK);
                    } else {
                        return response()->json([
                            'code' => 200,
                            'message' => 'Persona no encontrada',
                            'duplicado' => 'no',
                        ], Response::HTTP_OK);
                    }
                }
                // If status is not 200, continue to retry
                $lastException = new \Exception('HTTP Status: ' . $response->status());
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                $lastException = $e;
                // Wait before retry (except on last attempt)
                if ($attempt < $maxRetries) {
                    usleep(500000); // Wait 0.5 seconds before retry
                    continue;
                }
            } catch (\Exception $e) {
                $lastException = $e;
                // Wait before retry (except on last attempt)
                if ($attempt < $maxRetries) {
                    usleep(500000); // Wait 0.5 seconds before retry
                    continue;
                }
            }
        }

        // All retries failed, return error based on last exception
        if ($lastException instanceof \Illuminate\Http\Client\ConnectionException) {
            return response()->json([
                'code' => 503,
                'message' => 'No se pudo conectar al servicio externo después de ' . $maxRetries . ' intentos: ' . $lastException->getMessage(),
                'name' => '',
                'duplicado' => 'no',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        } else {
            return response()->json([
                'code' => 400,
                'message' => 'Error al consultar datos después de ' . $maxRetries . ' intentos: ' . ($lastException ? $lastException->getMessage() : 'Error desconocido'),
                'name' => '',
                'duplicado' => 'no',
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}

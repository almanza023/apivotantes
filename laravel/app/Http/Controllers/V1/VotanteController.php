<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\CargueMasivo;
use App\Models\Votante;
use App\Models\Persona;
use App\Models\VotanteOld;
use Illuminate\Http\Request;
use JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class VotanteController extends Controller
{
    protected $user;
    protected $model;

    public function __construct(Request $request)
    {
        $token = $request->header('Authorization');
        $this->model=Votante::class;
        if($token != ''){
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
        $objeto=$this->model::get();
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


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Validamos los datos
        $data = $request->only('tipo_persona', 'barrio', 'municipio', 'user', 'lider','sublider',
        'numerodocumento', 'nombrecompleto', 'fecha_expedicion', 'telefono', 'puesto', 'mesa');

        $validator = Validator::make($data, [
            'barrio' => 'required|numeric',
            'user' => 'required|numeric',
            'numerodocumento' => 'required|numeric|min:6|unique:votantes',
            'nombrecompleto' => 'required|max:200|string',
            'telefono' => 'required|numeric|min:9',
            //'puesto' => 'required|numeric',
            'mesa' => 'required|numeric',
        ]);

        //Si falla la validación
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        //Creamos el producto en la BD
        $objeto = $this->model::create([
            'barrio_id' => $request->barrio,
            'municipio_id' => $request->municipio,
            'user_id' => $request->user,
            'lider_id' => $request->lider,
            'sublider_id' => $request->sublider,
            'numerodocumento' => $request->numerodocumento,
            'nombrecompleto' => $request->nombrecompleto,
            'fecha_expedicion'=>$request->fecha_expedicion,
            'telefono'=>$request->telefono,
            'puesto_id'=>$request->puesto,
            'mesa'=>$request->mesa,
        ]);

        //Respuesta en caso de que todo vaya bien.
        return response()->json([
            'code'=>200,
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
                'code'=>200,
                'message' => 'Registro no encontrado en la base de datos.'
            ], 404);
        }

        //Si hay producto lo devolvemos
        return response()->json([
            'code'=>200,
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
        $data = $request->only('tipo_persona', 'barrio', 'municipio', 'user', 'lider', 'sublider',
        'numerodocumento', 'nombrecompleto', 'fecha_expedicion', 'telefono', 'puesto', 'mesa', 'lidernuevo', 'sublidernuevo', 'motivollamada');
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
        if(!empty($request->lidernuevo)) {
            $objeto->update([
            'lider_id' => $request->lidernuevo,
            'sublider_id' => $request->sublidernuevo,
            'numerodocumento' => $request->numerodocumento,
            'nombrecompleto' => $request->nombrecompleto,
            'telefono'=>$request->telefono,
            'motivollamada'=>$request->motivollamada,
            'usuarioactualiza'=>$this->user->username,
            'fechaactualiza'=>Carbon::now()->format('Y-m-d H:i:s')
        ]);
        }else{
            $objeto->update([
            'numerodocumento' => $request->numerodocumento,
            'nombrecompleto' => $request->nombrecompleto,
            'telefono'=>$request->telefono,
            'motivollamada'=>$request->motivollamada,
            'usuarioactualiza'=>$this->user->username,
            'fechaactualiza'=>Carbon::now()->format('Y-m-d H:i:s')
        ]);
        }

        //Devolvemos los datos actualizados.
        return response()->json([
            'code'=>200,
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
            'code'=>200,
            'message' => 'Registro Eliminado'
        ], Response::HTTP_OK);
    }

    public function cambiarEstado(Request $request)
    {
        //Validación de datos
        $data = $request->only('id');
        $validator = Validator::make($data, [
            'id' => 'required'          ]);
        //Si falla la validación error.
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }
        $objeto = $this->model::findOrfail($request->id);
        if($objeto->estado==1){
            $objeto->estado=2;
            $objeto->save();
        }else{
            $objeto->estado=1;
            $objeto->save();
        }
        //Devolvemos los datos actualizados.
        return response()->json([
            'code'=>200,
            'message' => 'Estado Actualizado Extiosamente',
            'data' => $objeto
        ], Response::HTTP_OK);
    }


    public function validarDocumento($documento)
    {
        //Listamos todos los productos

        $objeto=$this->model::validarDocumento($documento);
       if($objeto){
        return response()->json([
            'code'=>200,
            'data' => $objeto
        ], Response::HTTP_OK);
       }else{

        $validarLider=Persona::validarDuplicado($documento);
        if(!empty($validarLider)){
           return response()->json([
            'code'=>200,
            'data' =>$validarLider
        ], Response::HTTP_OK);
        }else{
             return response()->json([
            'code'=>200,
            'data' => []
        ], Response::HTTP_OK);
        }

       }

    }

    public function filtros(Request $request){

        $objeto = Votante::query()
        ->when($request->lider, fn($query, $lider) => $query->where('lider_id', $lider))
        ->when($request->sublider, fn($query, $sublider) => $query->where('sublider_id', $sublider))
        ->when($request->municipio, fn($query, $municipio) => $query->where('municipio_id', $municipio))
        ->when($request->barrio, fn($query, $barrio) => $query->where('barrio_id', $barrio))
        ->when($request->puesto, fn($query, $puesto) => $query->where('puesto_id', $puesto))
        ->with([
            'lider:id,nombrecompleto',
            'sublider:id,nombrecompleto',
            'barrio:id,descripcion',
            'puesto:id,descripcion',
            'municipio:id,descripcion'
        ])
        ->select('id', 'nombrecompleto', 'numerodocumento', 'telefono', 'mesa', 'barrio_id', 'lider_id', 'sublider_id', 'puesto_id', 'municipio_id', 'created_at')
        ->get();

        if($objeto){

            $responseArray=[];
            foreach ($objeto as $item) {
                $tempArray=[
                    'id'=>$item->id,
                    'nombrecompleto'=>$item->nombrecompleto ?? '',
                    'numerodocumento'=>$item->numerodocumento ?? '',
                    'telefono'=>$item->telefono ?? '',
                    'puesto'=>$item->puesto->descripcion ?? '',
                    'mesa'=>$item->mesa ?? '',
                    'barrio'=>$item->barrio->descripcion ?? '',
                    'lider'=>$item->lider->nombrecompleto ?? '',
                    'municipio'=>$item->municipio->descripcion,
                    'fecha_creacion'=>$item->created_at ? $item->created_at->format('d M Y - H:i:s') : '',
                    'municipiovotacion'=>$item->muncipio,
                    'puestovotacion'=>$item->puestovotacion,
                    'mesavotacion'=>$item->mesavotacion,

                ];
                if($item->sublider){
                    $tempArray['sublider']=$item->sublider->nombrecompleto ?? '';
                }
                array_push($responseArray, $tempArray);
            }
            return response()->json([
                'code'=>200,
                'data' => $responseArray
            ], Response::HTTP_OK);
           }else{
            return response()->json([
                'code'=>400,
                'data' => []
            ], Response::HTTP_BAD_REQUEST);
           }

    }

    public function tranferirVotantes(Request $request){
        $lider=$request->lider;
        $sublider=$request->sublider;
        $lider_mov=$request->lider_mov;
        $resultado=Votante::transferirVotantes($lider, $sublider, $lider_mov );
       if($resultado){
        return response()->json([
            'code'=>200,
            'message' => "Votantes Transferidos Exitosamente"
        ], Response::HTTP_OK);
       }else{
        return response()->json([
            'code'=>200,
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

        $documento=$request->documento;
        $date = Carbon::now()->format('Y-m-d');
        //Buscamos el producto
        $objeto = $this->model::where('numerodocumento', $documento)->first();

        //Actualizamos el producto.
        $objeto->update([
            'departamento' => $request->departamento,
            'municipio' => $request->municipio,
            'puestovotacion' => $request->puestovotacion,
            'direccion' => $request->direccion,
            'mesavotacion'=>$request->mesavotacion,
            'fechapuesto'=>$date,
            'estado'=>3,
            'usuariosube'=>$request->usuariosube
        ]);
        if($objeto){
            //Devolvemos los datos actualizados.
        return response()->json([
            'code'=>200,
            'message' => 'OK',
        ], Response::HTTP_OK);
        }else{
              return response()->json([
            'code'=>400,
            'message' => 'ERROR',
        ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function votantesSinPuesto(Request $request)
    {
        //Listamos todos los productos
        $objeto=$this->model::getVotantesPuesto($request->fecha1, $request->fecha2);

       if($objeto){
        return response()->json([
            'code'=>200,
            'data' => $objeto
        ], Response::HTTP_OK);
       }else{
        return response()->json([
            'code'=>400,
            'data' => []
        ], Response::HTTP_BAD_REQUEST);
       }

    }
    public function getDigitados($usuario)
    {
        //Listamos todos los productos
      $objeto=DB::select("select count(*) as total from votantes v where v.puestovotacion is not null and v.usuariosube=? and v.estado=3", [$usuario]);
       if($objeto){
        return response()->json([
            'code'=>200,
            'data' => $objeto[0]
        ], Response::HTTP_OK);
       }else{
        return response()->json([
            'code'=>200,
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

        $documento=$request->documento;
        $date = Carbon::now()->format('Y-m-d');
        //Buscamos el producto
        $objeto = $this->model::find($request->id);

        //Actualizamos el producto.
        $objeto->update([
            'confirmado' => "SI",
            'ip' => "",
            'fechaconfirmado' => $date,
        ]);
        if($objeto){
            //Devolvemos los datos actualizados.
        return response()->json([
            'code'=>200,
            'message' => 'Registro Confirmado',
        ], Response::HTTP_OK);
        }else{
              return response()->json([
            'code'=>400,
            'message' => 'ERROR',
        ], Response::HTTP_BAD_REQUEST);
        }
    }

     public function validarDocumentoConfirmacion($id, $puesto)
    {
        //Listamos todos los productos
       if (strpos($id, "S")){
           $idnuevo = trim($id, "S");
           $objeto=Persona::validarConfirmacion($idnuevo, $puesto);
       }else{
            $objeto = $this->model::validarConfirmacion($id, $puesto);
       }
        //Si el producto no existe devolvemos error no encontrado
        if (!$objeto) {
            return response()->json([
                'code'=>200,
                'message' =>[]
            ], 404);
        }

        //Si hay producto lo devolvemos
        return response()->json([
            'code'=>200,
            'data' => $objeto
        ], Response::HTTP_OK);

    }

    public function totalConfirmadosUsuario($usuario)
    {
        //Listamos todos los productos
       //Bucamos el producto
        $objeto = $this->model::getTotalConfirmadoUsuario($usuario);
        return response()->json([
                'code'=>200,
                'data' => $objeto
            ], 200);

    }

    public function votantesSinPuestoFecha(Request $request)
    {
        $documentos=$request->documentos;

    }

    public function cargarVotantes(Request $request)
    {
        $votantes = $request->input('votantes', []);
        $cargados=0;
        $errores=0;
        $validados=0;
        $cargados=count($votantes);
        $arrayErrores=[];
        DB::beginTransaction();
        try {
            if($cargados >0 ){
                $cargueMasivo=CargueMasivo::create([
                    'total' => $cargados,
                    'errores' => $errores,
                    'exitosos' => $validados,
                    'usuario' => $this->user->username,
                ]);
            }
            foreach ($votantes as $votante) {
                $validar=Votante::with(['lider', 'sublider', 'municipio'])
                ->where('numerodocumento', $votante['numerodocumento'])->first();
                if($validar){
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
                    'idcarguemasivo'=>$cargueMasivo->id
                ]);
                //Buscar en VotanteOld
            // $votanteOld = VotanteOld::where('numerodocumento', $votante->numerodocumento)->first();
            // if ($votanteOld) {
            //     $votante->nombrecompleto = $votanteOld->nombrecompleto;
            //     $votante->departamento = $votanteOld->departamento;
            //     $votante->municipio = $votanteOld->municipio;
            //     $votante->puestovotacion = $votanteOld->puestovotacion;
            //     $votante->direccion = $votanteOld->direccion;
            //     $votante->mesavotacion = $votanteOld->mesavotacion;
            //     $votante->fechapuesto = Carbon::now()->format('Y-m-d');
            //     $votante->apiname = true;
            //     $votante->fechaapiname=Carbon::now()->format('Y-m-d H:i:s');
            //     $votante->estado=1;
            //     $votante->save();
            // }
                $validados++;
            }
            //Actualizar cargue masivo
            $cargueMasivo->errores=$errores;
            $cargueMasivo->exitosos=$validados;
            $cargueMasivo->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'code'=>400,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
        $mensaje="Cargados: ".$cargados." Validados: ".$validados." Errores: ".$errores;
        $data=Votante::where('idcarguemasivo', $cargueMasivo->id)->get();
        return response()->json([
            'code'=>200,
            'message' => $mensaje,
            'carguemasivo' => $cargueMasivo,
            'detailError'=>$arrayErrores,
            'data'=>$data
        ], Response::HTTP_OK);
    }




    public function actualizarNombreAPI(Request $request)
    {
        $idcarguemasivo=$request->idcarguemasivo;
        $client = new \GuzzleHttp\Client();
        $processed = 0;
        $updated = 0;
        $failed = 0;
        $votantes=Votante::where('idcarguemasivo', $idcarguemasivo)
        ->where(function($query) {
            $query->whereNull('nombrecompleto')
                  ->orWhere('nombrecompleto', '')
                  ->orWhereNull('departamento')
                  ->orWhere('departamento', '');
        })->get();

        $processed=count($votantes);
        $arrayNuips=[];
        $mensaje='Operación se esta realizando en Segundo Plano Dar Click en Actualizar para ver el progreso';
        foreach ($votantes as $votante) {
            if(!empty($votante->nombrecompleto) && !empty($votante->departamento)){
                continue;
            }
            array_push($arrayNuips, $votante->numerodocumento);
        }
        if(count($arrayNuips) > 0){
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
                        'enviarapi'=>true
                    ]
                ]);
                if ($response->getStatusCode() != 200) {
                }
            $respuesta = json_decode($response->getBody()->getContents());
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                $failed++;
            }
        }else{
            $mensaje="No hay votantes para actualizar";
        }
        $votantes=Votante::where('idcarguemasivo', $idcarguemasivo)->get();
        return response()->json([
            'code'=>200,
            'message' => $mensaje,
            'exitosos' => $updated,
            'total' => $processed,
            'errores' => $failed,
            'data'=>$votantes
        ], Response::HTTP_OK);
    }

    public function obtenerVotantesByCargue(Request $request)
    {
        $idcarguemasivo=$request->idcarguemasivo;
        $data=Votante::where('idcarguemasivo', $idcarguemasivo)->get();
        $carguemasivo=CargueMasivo::where('id', $idcarguemasivo)->first();
        $mensaje="Cargados: ".$carguemasivo->total." Validados: ".$carguemasivo->exitosos." Errores: ".$carguemasivo->errores;

        return response()->json([
            'code'=>200,
            'message' => $mensaje,
            'carguemasivo' => $carguemasivo,
            'data'=>$data
        ], Response::HTTP_OK);
    }



}

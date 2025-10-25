<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Persona;
use App\Models\Votante;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class PersonaController extends Controller
{
    protected $user;
    protected $model;

    public function __construct(Request $request)
    {
        $token = $request->header('Authorization');
        $this->model=Persona::class;
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
        $objeto=$this->model::getDataLideres($this->user->municipio_id);
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
        $data = $request->only('tipo_persona', 'barrio', 'numerodocumento',
        'nombrecompleto', 'telefono', 'lider', 'municipio_id');
        $validator = Validator::make($data, [
            'tipo_persona' => 'required|numeric',
            'barrio' => 'required|numeric',
            'numerodocumento' => 'required|numeric|min:6',
            'nombrecompleto' => 'required|max:200|string',
        ]);

        //Si falla la validación
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        //Creamos el producto en la BD
        $objeto = $this->model::create([
            'tipo_persona_id' => $request->tipo_persona,
            'barrio_id' => $request->barrio,
            'lider_id' => $request->lider,
            'municipio_id' => $this->user->municipio_id,
            'numerodocumento' => $request->numerodocumento,
            'nombrecompleto' => $request->nombrecompleto,
            'telefono' => $request->telefono,
        ]);

        //Respuesta en caso de que todo vaya bien.
        return response()->json([
            'code'=>200,
            'message' => 'Registro Creado Exitosamente',
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
       $objeto = $this->model::find($id);

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
        $data = $request->only('tipo_persona', 'barrio', 'numerodocumento',
        'nombrecompleto', 'telefono', 'lider', 'municipio_id');
        $validator = Validator::make($data, [
            'telefono' => 'required',
            'nombrecompleto' => 'required|max:200|string',
        ]);

        //Si falla la validación error.
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        //Buscamos el producto
        $objeto = $this->model::findOrfail($id);

        $objeto->update([
            'numerodocumento' => $request->numerodocumento,
            'nombrecompleto' => $request->nombrecompleto,
            'telefono' => $request->telefono,
        ]);

        //Respuesta en caso de que todo vaya bien.
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

        $votantesLider=count(Votante::where('lider_id', $id)->get());
        $votantesSublider=count(Votante::where('sublider_id', $id)->get());
        $eliminar=false;
        if($votantesLider==0){
            $eliminar=true;
        }
        if($votantesSublider==0){
            $eliminar=true;
        }
       if($eliminar){
             //Eliminamos el producto
            $objeto->delete();
            //Devolvemos la respuesta
            return response()->json([
                'code'=>200,
                'message' => 'Registro Eliminado'
            ], Response::HTTP_OK);
       }else{
        return response()->json([
            'code'=>400,
            'message' => 'No se puede eliminar por que tiene Votantes asociados '
        ], Response::HTTP_OK);
       }
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

    public function activos()
    {
        //Listamos todos los registros activos
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
        ], Response::HTTP_BAD_REQUEST);
       }

    }

    public function showLideresySublideres($id)
    {
        //Listamos todos los registros activos
        $objeto=$this->model::getLideresySublider($id, $this->user->municipio_id);
       if($objeto){
        return response()->json([
            'code'=>200,
            'data' => $objeto
        ], Response::HTTP_OK);
       }else{
        return response()->json([
            'code'=>200,
            'data' => []
        ], Response::HTTP_BAD_REQUEST);
       }

    }

    public function getSublideres($id)
    {
        //Listamos todos los registros activos
        $objeto=$this->model::getSublideres($id);
       if($objeto){
        return response()->json([
            'code'=>200,
            'data' => $objeto
        ], Response::HTTP_OK);
       }else{
        return response()->json([
            'code'=>200,
            'data' => []
        ], Response::HTTP_BAD_REQUEST);
       }

    }

    public function validarDuplicado($id)
    {
        //Listamos todos los registros activos
        $objeto=$this->model::validarDuplicado($id);
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

    public function detalleSublideres($id)
    {
        //Listamos todos los registros activos
        $objeto=$this->model::getDataSubLideres($id);
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

    public function getEstadisticas($id)
    {
        //Listamos todos los registros activos
        $objeto=$this->model::getEstadisticas($id);
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


    public function getVotantes($id, $tipo)
    {
        //Listamos todos los registros activos
        $objeto=$this->model::getVotantes($id, $tipo);
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

    public function getCantidadVotos(Request $request){
        $lider=$request->lider;
        $sublider=$request->sublider;
        $cantidadVotos=Votante::cantidadVotos($lider, $sublider );
        return response()->json([
            'code'=>200,
            'data' => $cantidadVotos
        ], Response::HTTP_OK);
    }

    public function personasSinPuesto(Request $request)
    {
        //Listamos todos los productos
        $objeto=$this->model::getPersonasPuesto($request->fecha1, $request->fecha2);

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
        $objeto = Persona::where('numerodocumento', $documento)->first();

        //Actualizamos el producto.
        $objeto->update([
            'departamento' => $request->departamento,
            'municipio' => $request->municipio,
            'puestovotacion' => $request->puestovotacion,
            'direccion' => $request->direccion,
            'mesavotacion'=>$request->mesavotacion,
            'fechapuesto'=>$date,
            'estado'=>4,
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

    public function getDigitados($usuario)
    {
        //Listamos todos los productos
      $objeto=DB::select("select count(*) as total from personas v where v.puestovotacion is not null and v.usuariosube=? and v.estado=4", [$usuario]);
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

}

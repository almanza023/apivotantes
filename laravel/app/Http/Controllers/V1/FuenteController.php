<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\FuenteData;
use App\Models\Persona;
use Illuminate\Http\Request;
use JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class FuenteController extends Controller
{
    protected $user;
    protected $model;

    public function __construct(Request $request)
    {
        //$token = $request->header('Authorization');
        $this->model=FuenteData::class;
        //if($token != '')
            //En caso de que requiera autentifiación la ruta obtenemos el usuario y lo almacenamos en una variable, nosotros no lo utilizaremos.
            //$this->user = JWTAuth::parseToken()->authenticate();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

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
        $total=0;
        foreach ($request->data as $item) {
            $papellido="";
            $sapellido="";
            $pnombre="";
            $pnombre="";
            $snombre="";

            if($item["papellido"]){
                $papellido=$item["papellido"];
            }
            if($item["sapellido"]){
                $sapellido=$item["sapellido"];
            }
            if($item["pnombre"]){
                $pnombre=$item["pnombre"];
            }
            if($item["snombre"]){
                $snombre=$item["snombre"];
            }
            $nombrecompleto=$pnombre.' '.$snombre.' '.$papellido.' '.$sapellido;
            $objeto = $this->model::updateOrCreate([
                'numerodocumento' => $item["numerodocumento"],
            ],[
                'tipodocumento' => $item["tipodocumento"],
                'numerodocumento' => $item["numerodocumento"],
                'papellido' => $papellido,
                'sapellido' => $sapellido,
                'pnombre' => $pnombre,
                'snombre' => $snombre,
                'nombrecompleto' => $nombrecompleto,
                'estado' => $item["estado"],
                'lider' => $item["lider"],
            ]);
            if($objeto){
                $total++;
            }

        }
        //Creamos el producto en la BD

        //Respuesta en caso de que todo vaya bien.
        return response()->json([
            'code'=>200,
            'message' => 'Registro Creado Exitosamente '.$total,
        ], Response::HTTP_OK);
    }

    public function storeIndividual(Request $request)
    {
        //Validamos los datos
            $tipodocumento=$request->tipodocumento;
            $numerodocumento=$request->numerodocumento;
            $papellido=$request->papellido;
            $papellido=$request->papellido;
            $sapellido=$request->sapellido;
            $pnombre=$request->pnombre;
            $snombre=$request->snombre;
            $estado=$request->estado;
            $lider=$request->lider;
            $nombrecompleto=$pnombre.' '.$snombre.' '.$papellido.' '.$sapellido;
            $objeto = $this->model::updateOrCreate([
                'numerodocumento' => $numerodocumento,
            ],[
                'tipodocumento' => $tipodocumento,
                'numerodocumento' => $numerodocumento,
                'papellido' => $papellido,
                'sapellido' => $sapellido,
                'pnombre' => $pnombre,
                'snombre' => $snombre,
                'nombrecompleto' => $nombrecompleto,
                'estado' => $estado,
                'lider' => $lider,
            ]);
            if($objeto){
                return response()->json([
                    'code'=>200,
                    'message' => 'OK',
                ], Response::HTTP_OK);
            }else{
                return response()->json([
                    'code'=>400,
                    'message' => 'error',
                ], Response::HTTP_BAD_REQUEST);
            }


    }

    public function storeManual(Request $request)
    {
        //Validamos los datos
        $data = $request->only('tipodocumento',  'numerodocumento',
        'nombrecompleto');
        $validator = Validator::make($data, [
            'numerodocumento' => 'required',
            'nombrecompleto' => 'required',
        ]);

        //Si falla la validación
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        //Creamos el producto en la BD
        $objeto = $this->model::create([
            'tipodocumento' => "CC",
            'numerodocumento' => $request->numerodocumento,
            'nombrecompleto' => strtoupper($request->nombrecompleto),
            'estado' => "Agreado Manual",
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

    public function getErrores($fecha, $fecha2, $lider)
    {
        //Listamos todos los registros activos
        $objeto=$this->model::getErrores($fecha, $fecha2, $lider);
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

    public function getEstadisticas($fecha, $fecha2, $lider)
    {
        //Listamos todos los registros activos
        $errores=count($this->model::getErrores($fecha, $fecha2, $lider));
        $validos=count($this->model::getValidos($fecha, $fecha2, $lider));
        $total=$validos+$errores;
        $objeto=[
            "total"=>$total,
            "validos"=>$validos,
            "errores"=>$errores
        ];
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

    public function update(Request $request, $id)
    {

        //Validamos los datos
        $data = $request->only( 'id', 'tipodocumento',  'numerodocumento',
        'nombrecompleto');
        $validator = Validator::make($data, [
            'numerodocumento' => 'required',
            'nombrecompleto' => 'required',
        ]);

        //Si falla la validación
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }
        $objeto = $this->model::findOrfail($id);
        //Creamos el producto en la BD
        $objeto->update([
            'tipodocumento' => "CC",
            'numerodocumento' => $request->numerodocumento,
            'nombrecompleto' => strtoupper($request->nombrecompleto),
            'estado' => "Actualizado Manual",
        ]);

        //Respuesta en caso de que todo vaya bien.
        return response()->json([
            'code'=>200,
            'message' => 'Registro Actualizado Exitosamente',
            'data' => $objeto
        ], Response::HTTP_OK);
    }












}

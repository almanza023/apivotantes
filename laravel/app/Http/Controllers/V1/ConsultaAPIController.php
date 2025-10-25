<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\FuenteData;
use App\Models\LogAPI;
use App\Models\Persona;
use App\Models\PeticionAPI;
use App\Models\ResultadoAPI;
use App\Models\Votante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use JWTAuth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class ConsultaAPIController extends Controller
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

    public function loginAPI(){
        // URL de la API
        $apiUrl = "https://demorcs.olimpiait.com:6314/TraerToken";
        // Datos que quieres enviar en la solicitud POST
        $postData = [
            'clientId' => 'FNH',
            'clientSecret' => 'AtGskrAB@',
            // Agrega más parámetros según necesites
        ];
        // Hacer la solicitud POST a la API
        $response = Http::post($apiUrl, $postData);
        // Verificar si la solicitud fue exitosa
        if ($response->successful()) {
            $responseData = $response->json();
            // Procesa los datos de respuesta según necesites
            return $responseData['accessToken'];
        } else {
            return "";
        }
   }

    public function obtenerTokenSolicitud($numerodocumento){
        // URL de la API y datos de autenticación
        $apiUrl = "https://demorcs.olimpiait.com:6314/Validacion/SolicitudFuentesAbiertas";
        $guidConv="7d831870-1d25-414b-8ac9-600153750b95";
        $accessToken = $this->loginAPI();
        $robot=[
            "LVREGI",
            "CEREGI"
        ];
        $postData = [
            'guidConv' => $guidConv,
            'tipoDoc' => 'CC',
            'documento' =>$numerodocumento,
            'robot'=>$robot,
            "usuario"=> "FNH",
            "clave"=> "12345678"
            // Agrega más parámetros según necesites
        ];

        // Hacer la solicitud a la API con el token de autenticación
        $response = Http::withToken($accessToken)->post($apiUrl, $postData );


        // Verificar si la solicitud fue exitosa
        if ($response->successful()) {
            $data = $response->json();
            $transaccionGuid=$data['data']['transaccionGuid'];
                // Guardar en la base de datos
                LogAPI::create([
                    'respuesta'=>json_encode($data),
                    'operacion'=>"SolicitudFuentesAbiertas",
                ]);
                return $transaccionGuid;
        } else {
           return "";
        }
    }

    public function consultarFuente(Request $request){
        $data = $request->only('numerodocumento');
        $validator = Validator::make($data, [
            'numerodocumento' => 'required|numeric|min:6',
        ]);

        //Si falla la validación
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }

        $validarDocumento=ResultadoAPI::validarDocumento($request->numerodocumento);
        if(!empty($validarDocumento)){
            return response()->json(['code'=>'200', 'message'=>'Exitoso', 'data' => $validarDocumento]);
        }
        // URL de la API y datos de autenticación
        $apiUrl = "https://demorcs.olimpiait.com:6314/Validacion/ConsultarFuentesAbiertas";
        $guidConv="7d831870-1d25-414b-8ac9-600153750b95";
        $accessToken = $this->loginAPI();
        $fuentesAbiertasGuid= $this->obtenerTokenSolicitud($request->numerodocumento);
        $usuario="FNH";
        $clave= "12345678";
        if($fuentesAbiertasGuid!="" && $accessToken!=""){
            $postData = [
                'guidConv' => $guidConv,
                'fuentesAbiertasGuid' => $fuentesAbiertasGuid,
                'tipoDoc' => 'CC',
                'documento' =>$request->documento,
                "usuario"=> $usuario,
                "clave"=> $clave
                // Agrega más parámetros según necesites
            ];
            // Hacer la solicitud a la API con el token de autenticación
            $response = Http::withToken($accessToken)->post($apiUrl, $postData );

            // Verificar si la solicitud fue exitosa
            if ($response->successful()) {
                $data = $response->json();
                LogAPI::create([
                    'respuesta'=>json_encode($data),
                    'operacion'=>"ConsultarFuentesAbiertas",
                ]);

                $fuentesRaw = $data["data"]['fuentesRaw'];
                $departamento="";
                $municipio="";
                $mesa="";
                $puesto="";
                $direccion="";
                    foreach ($fuentesRaw as $item) {
                        if($item["codigo"]=="CEREGI"){
                            $estado=$item["data"]["texto"]["estado"];
                            $fecha_expe=$item["data"]["texto"]["fecha_expe"];
                            $lugar_expe=$item["data"]["texto"]["lugar_expe"];
                            $nombre_expe=$item["data"]["texto"]["nombre_expe"];
                        }
                        if($item["codigo"]=="LVREGI"){
                            $departamento=$item["data"]["texto"]["departamento"];
                            $direccion=$item["data"]["texto"]["direccion"];
                            $mesa=$item["data"]["texto"]["mesa"];
                            $municipio=$item["data"]["texto"]["municipio"];
                            $nuip=$item["data"]["texto"]["nuip"];
                            $puesto=$item["data"]["texto"]["puesto"];
                        }
                    }
                    $responseData=[
                        "numeroDocumento"=>$request->numerodocumento,
                        "nombre_expe"=>$nombre_expe,
                        "fecha_expe"=>$fecha_expe,
                        "lugar_expe"=>$lugar_expe,
                        "estado_expe"=>$estado,
                        "departamento"=>$departamento,
                        "municipio"=>$municipio,
                        "mesa"=>$mesa,
                        "puesto"=>$puesto,
                        "direccion"=>$direccion,
                    ];
                    ResultadoAPI::updateOrCreate([
                        //Add unique field combo to match here
                        //For example, perhaps you only want one entry per user:
                        'numerodocumento'   =>$responseData['numeroDocumento']
                    ],[
                        'numerodocumento'   =>$responseData['numeroDocumento'],
                        'nombre_expe'   =>$responseData['nombre_expe'],
                        'fecha_expe'   =>$responseData['fecha_expe'],
                        'lugar_expe'   =>$responseData['lugar_expe'],
                        'estado_expe'   =>$responseData['estado_expe'],
                        'departamento'   =>$responseData['numeroDocumento'],
                        'municipio'   =>$responseData['municipio'],
                        'mesa'   =>$responseData['mesa'],
                        'puesto'   =>$responseData['puesto'],
                        'direccion'   =>$responseData['direccion'],
                    ]);
                    PeticionAPI::find(1)->increment('exitosas_nombres');
                    return response()->json(['code'=>'200', 'message'=>'Exitoso', 'data' => $responseData]);

                } else {
                    return response()->json(['code'=>'400', 'message'=>'No se encontrarón resultado en la consulta a la API',  'data'=>[]]);
                }
        }else{
            return response()->json(['code'=>'400', 'message'=>'No se genero ID para consultar',  'data'=>[]]);

        }

    }

    public function validarDocumentoAPI($documento)
    {
        //Listamos todos los productos

        $objeto=ResultadoAPI::validarDocumento($documento);
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

    public function validarFuente(Request $request )
    {
        $data = $request->only('numerodocumento');
        $validator = Validator::make($data, [
            'numerodocumento' => 'required|numeric|min:6',
        ]);

        //Si falla la validación
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 400);
        }
        $documento=$request->numerodocumento;
        $objeto=FuenteData::getByDocumento($documento);
        if(!empty($objeto)){
            return response()->json([
                'code'=>200,
                'data' => $objeto
            ], Response::HTTP_OK);
        }else{
            return response()->json([
                'code'=>400,
                'message'=>"No existen datos",
                'data' => []
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}





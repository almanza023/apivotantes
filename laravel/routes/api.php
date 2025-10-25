<?php

use App\Http\Controllers\V1\ProductsController;
use App\Http\Controllers\V1\AuthController;
use App\Http\Controllers\V1\BarrioController;
use App\Http\Controllers\V1\CampannaController;
use App\Http\Controllers\V1\ConsultaAPIController;
use App\Http\Controllers\V1\EstadisticaController;
use App\Http\Controllers\V1\FuenteController;
use App\Http\Controllers\V1\MunicipioController;
use App\Http\Controllers\V1\PDFController;
use App\Http\Controllers\V1\TipoPersonaController;
use App\Http\Controllers\V1\VotanteController;
use App\Http\Controllers\V1\PersonaController;
use App\Http\Controllers\V1\PuestoVotacionController;
use App\Http\Controllers\V1\UsuarioController;
use App\Http\Controllers\V1\MotivoLlamadaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {
    //Prefijo V1, todo lo que este dentro de este grupo se accedera escribiendo v1 en el navegador, es decir /api/v1/*

    Route::post('login', [AuthController::class, 'authenticate']);
    Route::post('register', [AuthController::class, 'register']);

    Route::post('fuente/guardar', [FuenteController::class, 'storeIndividual']);
    Route::post('votante/puesto', [VotanteController::class, 'agregarPuesto']);
    Route::post('votante/listado', [VotanteController::class,'votantesSinPuesto']);
    Route::get('totaldigitados/{usuario}', [VotanteController::class, 'getDigitados']);
    Route::post('confirmar', [VotanteController::class, 'confirmarVoto']);

    Route::post('personas/listado', [PersonaController::class,'personasSinPuesto']);
    Route::post('personas/puesto', [PersonaController::class, 'agregarPuesto']);
    Route::get('totaldigitadospersonas/{usuario}', [VotanteController::class, 'getDigitados']);


    Route::group(['middleware' => ['jwt.verify']], function() {
        //Todo lo que este dentro de este grupo requiere verificaci��n de usuario.

        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('get-user', [AuthController::class, 'getUser']);

         //Barrios
        Route::get('barrios', [BarrioController::class, 'index']);
        Route::post('barrios', [BarrioController::class, 'store']);
        Route::get('barrios/{id}', [BarrioController::class, 'show']);
        Route::put('barrios/{id}', [BarrioController::class, 'update']);
        Route::delete('barrios/{id}', [BarrioController::class, 'destroy']);
        Route::post('barrios/cambiarEstado', [BarrioController::class, 'cambiarEstado']);
        Route::get('barrios-activos', [BarrioController::class, 'activos']);
        Route::get('barrios/municipio/{id}', [BarrioController::class, 'getByMunicipio']);

        //Municipios
        Route::get('municipios', [MunicipioController::class, 'index']);
        Route::post('municipios', [MunicipioController::class, 'store']);
        Route::get('municipios/{id}', [MunicipioController::class, 'show']);
        Route::put('municipios/{id}', [MunicipioController::class, 'update']);
        Route::delete('municipios/{id}', [MunicipioController::class, 'destroy']);
        Route::post('municipios/cambiarEstado', [MunicipioController::class, 'cambiarEstado']);
        Route::get('municipios-activos', [MunicipioController::class, 'activos']);


         //Tipo Personas
         Route::get('tipopersonas', [TipoPersonaController::class, 'index']);
         Route::post('tipopersonas', [TipoPersonaController::class, 'store']);
         Route::get('tipopersonas/{id}', [TipoPersonaController::class, 'show']);
         Route::put('tipopersonas/{id}', [TipoPersonaController::class, 'update']);
         Route::delete('tipopersonas/{id}', [TipoPersonaController::class, 'destroy']);
         Route::post('tipopersonas/cambiarEstado', [TipoPersonaController::class, 'cambiarEstado']);
         Route::get('tipopersonas-activos', [TipoPersonaController::class, 'activos']);


         //personas
         Route::get('personas', [PersonaController::class, 'index']);
         Route::post('personas', [PersonaController::class, 'store']);
         Route::get('personas/{id}', [PersonaController::class, 'show']);
         Route::put('personas/{id}', [PersonaController::class, 'update']);
         Route::delete('personas/{id}', [PersonaController::class, 'destroy']);
         Route::post('personas/cambiarEstado', [PersonaController::class, 'cambiarEstado']);
         Route::get('personas-activos', [PersonaController::class, 'activos']);
         Route::get('personas/lideres/{id}', [PersonaController::class, 'showLideresySublideres']);
         Route::get('personas/sublideres/{id}', [PersonaController::class, 'getSublideres']);
         Route::get('personas/validar/{id}', [PersonaController::class, 'validarDuplicado']);
         Route::get('personas/detallesSublideres/{id}', [PersonaController::class, 'detalleSublideres']);
         Route::get('personas/getEstadisticas/{id}', [PersonaController::class, 'getEstadisticas']);
         Route::get('personas/getVotantes/{id}/{tipo}', [PersonaController::class, 'getVotantes']);
         Route::post('personas/getVotos', [PersonaController::class, 'getCantidadVotos']);


           //Votantes
           Route::get('votantes', [VotanteController::class, 'index']);
           Route::post('votantes', [VotanteController::class, 'store']);
           Route::get('votantes/{id}', [VotanteController::class, 'show']);
           Route::put('votantes/{id}', [VotanteController::class, 'update']);
           Route::delete('votantes/{id}', [VotanteController::class, 'destroy']);
           Route::get('votantes-validar/{id}', [VotanteController::class, 'validarDocumento']);

           Route::post('votantes/filtros', [VotanteController::class, 'filtros']);
           Route::post('votantes/tranferir-votantes', [VotanteController::class, 'tranferirVotantes']);


          //Puesto Votacion
          Route::get('puestos', [PuestoVotacionController::class, 'index']);
          Route::post('puestos', [PuestoVotacionController::class, 'store']);
          Route::get('puestos/{id}', [PuestoVotacionController::class, 'show']);
          Route::put('puestos/{id}', [PuestoVotacionController::class, 'update']);
          Route::delete('puestos/{id}', [PuestoVotacionController::class, 'destroy']);
          Route::get('puestos-activos', [PuestoVotacionController::class, 'activos']);
          Route::post('puestos/cambiarEstado', [PuestoVotacionController::class, 'cambiarEstado']);

          //Campa�0�9as
          Route::get('campannas', [CampannaController::class, 'index']);
          Route::post('campannas', [CampannaController::class, 'store']);
          Route::get('campannas/{id}', [CampannaController::class, 'show']);
          Route::put('campannas/{id}', [CampannaController::class, 'update']);
          Route::delete('campannas/{id}', [CampannaController::class, 'destroy']);
          Route::post('campannas/cambiarEstado', [CampannaController::class, 'cambiarEstado']);
          Route::get('campannas-activos', [CampannaController::class, 'activos']);

          //Usuarios
          Route::get('usuarios', [UsuarioController::class, 'index']);
          Route::post('usuarios', [UsuarioController::class, 'store']);
          Route::get('usuarios/{id}', [UsuarioController::class, 'show']);
          Route::put('usuarios/{id}', [UsuarioController::class, 'update']);
          Route::delete('usuarios/{id}', [UsuarioController::class, 'destroy']);
          Route::get('usuarios-activos', [UsuarioController::class, 'activos']);
          Route::post('usuarios/cambiarEstado', [UsuarioController::class, 'cambiarEstado']);

          //Consulta API ReconocerAPI
          Route::post('consultarApi', [ConsultaAPIController::class, 'consultarFuente']);
          Route::post('validarDocumentoAPI', [ConsultaAPIController::class, 'validarDocumentoAPI']);
          Route::post('validarDocumentoFuente', [ConsultaAPIController::class, 'validarFuente']);

          //Reporte
          Route::post('reportes/votantes', [PDFController::class, 'votantesByLider']);
          Route::post('reportes/estadisticas', [PDFController::class, 'reporteLiderYSublider']);
          Route::post('reportes/puestos', [PDFController::class, 'reportePuestosVotacion']);
          Route::post('reportes/mesasvotacion', [PDFController::class, 'reportePuestoYMesa']);
          Route::post('reportes/votantes-codigo', [PDFController::class, 'votantesCodigosByLider']);
          Route::post('reportes/votantes-ticket', [PDFController::class, 'votantesTicketByLider']);



          //Fuente
          Route::post('fuente', [FuenteController::class, 'store']);
          Route::get('fuente/errores/{fecha}/{fecha2}/{tipo}', [FuenteController::class, 'getErrores']);
          Route::get('fuente/estadisticas/{fecha}/{fecha2}', [FuenteController::class, 'getEstadisticas']);
          Route::post('fuente-crear', [FuenteController::class, 'storeManual']);
          Route::put('fuente/{id}', [FuenteController::class, 'update']);
          Route::delete('fuente-eliminar/{id}', [FuenteController::class, 'destroy']);

          Route::get('estadisticas', [EstadisticaController::class, 'index']);
          Route::get('estadisticas-lideres', [EstadisticaController::class, 'getEstadisticasLideres']);

           Route::get('fuente/estadisticas/{fecha}/{fecha2}/{opcion}', [FuenteController::class, 'getEstadisticas']);

           //Motivo Llamadas
        Route::get('motivollamada', [MotivoLlamadaController::class, 'index']);
        Route::post('motivollamada', [MotivoLlamadaController::class, 'store']);
        Route::get('motivollamada/{id}', [MotivoLlamadaController::class, 'show']);
        Route::put('motivollamada/{id}', [MotivoLlamadaController::class, 'update']);
        Route::delete('motivollamada/{id}', [MotivoLlamadaController::class, 'destroy']);
        Route::post('motivollamada/cambiarEstado', [MotivoLlamadaController::class, 'cambiarEstado']);
        Route::get('motivollamada-activos', [MotivoLlamadaController::class, 'activos']);





    });
});

<?php

use App\Http\Controllers\Api\archivosController;
use App\Http\Controllers\Api\catserviciosController;
use App\Http\Controllers\Api\clinicasController;
use App\Http\Controllers\Api\detalle_ordenesController;
use App\Http\Controllers\Api\egresosController;
use App\Http\Controllers\Api\facturasController;
use App\Http\Controllers\Api\informesController;
use App\Http\Controllers\Api\insumoCarpetaController;
use App\Http\Controllers\Api\itemservicesController;
use App\Http\Controllers\Api\mailController;
use App\Http\Controllers\Api\mailPacientesController;
use App\Http\Controllers\Api\OdontologosController;
use App\Http\Controllers\Api\OrdenesController;
use App\Http\Controllers\Api\PacientesController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\userController;

// INICIAR SESSION
Route::post('/login', [userController::class, 'login']);
Route::post('/loginPaciente', [userController::class, 'loginClientes']);
// VER PERFIL
Route::middleware('auth:sanctum')->get('user-profile', [userController::class, 'userProfile']);
Route::middleware('auth:sanctum')->post('logout', [userController::class, 'logout']);
// VER RUTAS GENERALES
Route::middleware('auth:sanctum')->get('allServicios', [catserviciosController::class, 'index']);
Route::middleware('auth:sanctum')->get('/oneOrdenVirtual/{id}', [OrdenesController::class, 'show']);
Route::middleware('auth:sanctum')->get('/verImagenes', [archivosController::class, 'index']);
Route::middleware('auth:sanctum')->get('/veRutas', [archivosController::class, 'indexImages']);
Route::middleware('auth:sanctum')->get('/oneServicio/{id}', [catserviciosController::class, 'show']);
///ITEMS DE SERVICIOS
Route::middleware('auth:sanctum')->get('allItemServices', [itemservicesController::class, 'index']);
Route::middleware('auth:sanctum')->post('buscarItems', [itemservicesController::class, 'buscar']);

///ORDENES
Route::middleware('auth:sanctum')->post('saveOrdenVirtual', [OrdenesController::class, 'store']);
Route::middleware('auth:sanctum')->put('/updateOrdenVirtual/{id}', [OrdenesController::class, 'update']);

Route::middleware('auth:sanctum')->post('verificarOrden', [OrdenesController::class, 'verificacion']);
// DESCARGA DE ARCHIVOS E INFORMES
Route::middleware('auth:sanctum')->get('/dowloads/{id}', [archivosController::class, 'getImagenes']);
Route::middleware('auth:sanctum')->post('dowloadsGroup', [archivosController::class, 'getImagenesGroupServices']);
Route::middleware('auth:sanctum')->post('dowloadsGroupInformes', [informesController::class, 'getImagenesGroupServicesInformes']);
Route::middleware('auth:sanctum')->get('verInformes', [informesController::class, 'index']);
Route::middleware('auth:sanctum')->get('dowloadInformes/{id}', [informesController::class, 'dowloadInformes']);
Route::middleware('auth:sanctum')->get('verInfo/{id}', [informesController::class, 'show']);

// Route::middleware('auth:sanctum')->get('verInformes/{id}', [informesController::class, 'index']);
Route::post('/enviarCorreosPacietnes', [mailPacientesController::class, 'enviarCorreo']);
Route::post('/enviarCorreoFinal', [mailPacientesController::class, 'enviarCorreoFinal']);
Route::post('/enviarCorreoRecuperacion', [mailPacientesController::class, 'recuperarCuenta']);
Route::post('/validarCodigo', [mailPacientesController::class, 'validarCodigo']);
Route::post('/valasdad', [OdontologosController::class, 'store2']);
Route::post('/saveOdontologo', [OdontologosController::class, 'store']);
Route::post('/saveOdontologo3', [OdontologosController::class, 'store3']);

//PACIENTES
Route::middleware('auth:sanctum')->post('/validarPaciente', [PacientesController::class, 'eyes']);
Route::middleware('auth:sanctum')->post('/savePaciente', [PacientesController::class, 'store']);
Route::middleware('auth:sanctum')->post('/savePaciente2', [PacientesController::class, 'store2']);
Route::middleware('auth:sanctum')->get('/onePaciente/{id}', [PacientesController::class, 'show']);

// REPORTE

Route::group(['middleware' => ['auth:sanctum', 'role:99,98']], function () {
        // AMBOS
        Route::controller(clinicasController::class)->group(function(){
            Route::get('/allClinicas','index');
            Route::get('/allClinicas2','index2');
            Route::post('/saveClinica','store2');
            Route::get('/oneClinica/{id}','show');
            Route::put('/updateClinica/{id}','update');
            Route::delete('/deleteClinica/{id}','destroy');
            Route::post('/buscarClinica','buscar');
        });
        //AMBOS
        Route::controller(PacientesController::class)->group(function(){
            Route::get('/allPacientes','index');
            Route::post('/buscarPacientes','buscar');
            Route::put('/updatePaciente/{id}','update');
            Route::delete('/deletePaciente/{id}','destroy');
        });
        Route::controller(OdontologosController::class)->group(function(){
            Route::get('/allOdontologos','index');
            Route::post('/buscarOdontologo','buscar');
            Route::get('/oneOdontologo/{id}','show');
            Route::put('/updateOdontologo/{id}','update');
            Route::delete('/deleteOdontologo/{id}','destroy');
        });
        //AMBOS
        Route::controller(OrdenesController::class)->group(function(){
            Route::get('/allOrdenVirtuales','index');

            Route::get('/allOrdenesPerMes','indexPerMes');

            Route::post('/buscarOrdenes','buscar');
            Route::post('/buscarOrdenesPerFecha','buscarFechas');
            Route::post('/getCreados','buscarCreaadoswhereFechas');
            Route::post('/buscarCreaados','buscarCreaados');
            Route::post('/reporteOrdenes','indexReporte');
            Route::put('/updateOrdenFactura/{id}','updateFactura'); 
            Route::delete('/deleteOrdenVirtual/{id}','destroy');
        });
        //AMBOS
        Route::controller(archivosController::class)->group(function(){
            Route::post('/saveArchivos','store');
            Route::get('/oneArchivo/{id}','show');
            Route::delete('/deleteArchivos/{id}','destroy');
            Route::get('/destroyAll/{id}','destroyAll');
        });
        Route::controller(mailController::class)->group(function(){
            Route::post('/enviarCorreo','enviarCorreo');
        });
        Route::controller(informesController::class)->group(function(){
            // Route::get('/verInformes','index');
            Route::post('/saveInformes','store');
            Route::delete('/deleteInformes/{id}','destroy');
            Route::get('/dowloadsInformes/{id}','getInformes');
            Route::get('/destroyAllInformes/{id}','destroyAll');
        });
        // FALTA VER
        Route::controller(detalle_ordenesController::class)->group(function(){
            Route::get('/allDetallesOrdenes','index');
            Route::post('/saveDetalleOrden','store');
            // Route::get('/oneItem/{id}','show');
            // Route::put('/updateItem/{id}','update');
            // Route::delete('/deleteItem/{id}','destroy');
        });
        //AMBOS
        Route::controller(egresosController::class)->group(function(){
            Route::get('/allEgresos','index');
            Route::post('/buscarEgresos','buscar');
            Route::get('/reporteEgresos','reporte');
            Route::get('/reporteEgresosMes','reporteMes');
            Route::post('/reporteEgresosFechas','reporteFecha');
            Route::post('/saveEgresos','store');
            Route::get('/oneEgreso/{id}','show');
            Route::put('/upadateEgreso/{id}','update');
            Route::delete('/deleteEgreso/{id}','destroy');
        });
        //AMBOS
        Route::controller(facturasController::class)->group(function(){
            Route::post('/saveFactura','store');
            Route::get('/allFacturas','index');
            Route::get('/allFacturasID/{id}','indexID');
            Route::get('/oneFactura/{id}','show');
            Route::put('/updateFactura/{id}','update');
        });
});

Route::group(['middleware' => ["auth:sanctum", 'role: 0']], function () {
    // TRAER TODOS LOS RESULTADOS DE UN SOLO CLIENTE
    Route::middleware('auth:sanctum')->get('allOrdenVirtualesPacientes/{id}', [OrdenesController::class, 'indexClientes']);
});

Route::group(['middleware' => ["auth:sanctum", 'role: 1']], function () {
    // TRAER TODOS LOS RESULTADOS DE UN SOLO CLIENTE
    Route::middleware('auth:sanctum')->get('allOrdenesOdontologos/{id}', [OrdenesController::class, 'indexDoctores']);
});

Route::group(['middleware' => ['auth:sanctum', 'role:99']], function () {
    Route::controller(userController::class)->group(function(){
        // SOLO ADMINDISTRADOR
        Route::get('/getUsuarios','index');
        Route::get('/getUsuario/{id}','show');
        Route::post('/registerUsersAdmins','register');
        Route::put('/updateUser/{id}','update');
        Route::delete('/destroyUser/{id}','destroy');
    });
    //AMBOS
    Route::controller(OrdenesController::class)->group(function(){
        // SOLO ADMINDISTRADOR
        Route::get('/reporteOdontologos','obtenerClientesOdontologos');
        Route::get('/reporteOdontologosMes','obtenerClientesOdontologosMes');
        Route::post('/reporteOdontologosFechas','obtenerClientesOdontologosFechas');
        Route::get('/reporteClinicasPrimerMes','obtenerClinicasConOrdenesMes');
        Route::get('/reporteClinicas','obtenerClinicasConOrdenes');
        Route::post('/reporteClinicasFechas','obtenerClinicasConOrdenesFECHAS');
        Route::get('/reporteIngresos','reproteIngresos');
        Route::get('/reproteIngresosMes','reproteIngresosMes');
        Route::get('/reporteComisiones','reporteComisiones');
        Route::get('/reporteComisionesMes','reporteComisionesMes');
        
        Route::post('/reporteComisionesFechas','reporteComisionesFechas');
    });
    Route::controller(itemservicesController::class)->group(function(){
        Route::post('/saveItem','store');
        Route::get('/oneItem/{id}','show');
        Route::put('/updateItem/{id}','update');
        Route::delete('/deleteItem/{id}','destroy');
    });
    Route::controller(insumoCarpetaController::class)->group(function(){
        // Route::post('/saveItem','store');
        Route::get('/oneInsumo/{id}','show');
        Route::put('/updateInsumo/{id}','update');
        // Route::delete('/deleteItem/{id}','destroy');
    });
    Route::controller(catserviciosController::class)->group(function(){
        Route::post('/saveServicio','store');
        Route::put('/updateServicio/{id}','update');
        Route::delete('/deleteServicio/{id}','destroy');
    });
});
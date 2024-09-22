<?php

use App\Http\Controllers\Api\GradoAcademicoController;
use App\Http\Controllers\Api\AreaFormacionController;
use App\Http\Controllers\Api\InstitucionController;
use App\Http\Controllers\Api\FormacionController;
use App\Http\Controllers\Api\GerenciaDepartamentoController;
use App\Http\Controllers\Api\PuestoController;
use App\Http\Controllers\Api\RolController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ImportarExcelController;
use App\Http\Controllers\IncorporacionesController;
use App\Http\Controllers\PersonasController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\CarpetaController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ImportarImagesController;
use App\Http\Controllers\InterinatoController;
use App\Http\Controllers\PlanillaController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthenticatedSessionController::class, 'store']);

Route::middleware('auth')->group(function () {
  Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

// usuarios y roles
Route::group(['prefix' => 'users'], function () {
  Route::post('/listar-users', [UserController::class, 'listarUsers']);
  Route::get('/listar-users-dde', [UserController::class, 'listarUsersDDE']);
  Route::get('/{userId}/listar-user-roles', [RolController::class, 'listarUsersRoles']);
  Route::get('/listar-roles', [RolController::class, 'listarRoles']);
  
  Route::put('/updateRolUser/{userId}', [UserController::class, 'update']);
  Route::get('/{userId}/userRol', [UserController::class, 'obtenerRolUser']);
});

// administracion
Route::group(['prefix' => 'administracion'], function () {
  Route::post('/upload-excel-planilla', [ImportarExcelController::class, 'importarExcelPlanilla']);
  Route::post('/upload-imagenes-funcionarios', [ImportarImagesController::class, 'importarImgFuncionarios']);
  Route::get('{userCi}/img-user-administrador', [ImportarImagesController::class, 'getImgUserAdministrador']);
  Route::get('{userCi}/img-users', [ImportarImagesController::class, 'getImgUserAdministrador']);
  
});

Route::group(['prefix' => 'planilla'], function () {
  Route::post('/listar-puestos', [PlanillaController::class, 'listarPuestos']);
  Route::get('/{personaId}/imagen-funcionario', [PlanillaController::class, 'getImagenFuncionario']);
  Route::get('/listar-gerencia', [GerenciaDepartamentoController::class, 'GerenciaDepartamento']);
  Route::get('/{puestoId}/inf-persona-puesto', [PlanillaController::class, 'infPersonaPuesto']);
});

Route::group(['prefix' => 'interinatos'], function () {
  Route::post('/crear-interinato', [InterinatoController::class, 'crearInterinato']);
  Route::post('/listar-interinatos', [InterinatoController::class, 'listarInterinatos']); 
  Route::post('/filtrar-interinato', [InterinatoController::class, 'byFiltrosInterinatos']); 
  Route::get('/{interinatoId}/mostrar-modificar-interinato', [InterinatoController::class, 'mostrarModificarInterinato']);  
  Route::put('/{interinatoId}/modificar-interinato', [InterinatoController::class, 'modificarInterinato']);
});

// incorporaciones
Route::group(['prefix' => 'incorporaciones'], function () {

  Route::get('/{ciPersona}/by-ciPersona-inc', [IncorporacionesController::class, 'byCiPersonaFormIncorporacion']);
  Route::get('/{ciPersona}/by-ciPersona-camb-item', [IncorporacionesController::class, 'byCiPersonaFormCambioItem']);


  Route::put('/', [IncorporacionesController::class, 'crearActualizarIncorporacion']);
  Route::post('/listar-incorporaciones', [IncorporacionesController::class, 'listPaginateIncorporaciones']);
  Route::post('/filtrar-incorporaciones', [IncorporacionesController::class, 'byFiltrosIncorporacion']);
  Route::put('/{incorporacionId}/darBajaIncorporacion', [IncorporacionesController::class, 'darBajaIncorporacion']);
  Route::post('/genReportEval', [IncorporacionesController::class, 'genReportEvaluacion']);
  Route::post('/genReportTrimestral', [IncorporacionesController::class, 'genReportTrimestral']);
  Route::get('/{incorporacionId}/gen-inf-minuta', [IncorporacionesController::class, 'generarInfMinuta']);
  Route::get('/{incorporacionId}/gen-inf-nota', [IncorporacionesController::class, 'generarInfNota']);
  Route::get('/{incorporacionId}/gen-rap', [IncorporacionesController::class, 'generarRap']);
  Route::get('/{incorporacionId}/gen-memo', [IncorporacionesController::class, 'generarMemorandum']);
  Route::get('/{incorporacionId}/gen-acta-entrega', [IncorporacionesController::class, 'generarActaEntrega']);
  Route::get('/{incorporacionId}/gen-acta-posesion', [IncorporacionesController::class, 'generarActaPosesion']);
  Route::get('/{incorporacionId}/gen-form-R1418', [IncorporacionesController::class, 'generarFormR1418']);
  Route::get('/{incorporacionId}/gen-form-R1419', [IncorporacionesController::class, 'generarFormR1419']);
  Route::get('/{incorporacionId}/gen-form-R0980', [IncorporacionesController::class, 'generarFormR0980']);
  //formularios para incorporacion
  Route::get('/{incorporacionId}/gen-form-R0078', [IncorporacionesController::class, 'generarFormR0078']);
  Route::get('/{incorporacionId}/gen-form-R1401', [IncorporacionesController::class, 'generarFormR1401']);
  //formularios de cambio item
  Route::get('/{incorporacionId}/gen-form-R1023', [IncorporacionesController::class, 'generarFormR1023']);
  Route::get('/{incorporacionId}/gen-form-R1129', [IncorporacionesController::class, 'generarFormR1129']);
  // otros formularios de incorporacion
  Route::get('/{incorporacionId}/gen-R0716', [IncorporacionesController::class, 'generarR0716']);
  Route::get('/{incorporacionId}/gen-R0921', [IncorporacionesController::class, 'generarR0921']);
  Route::get('/{incorporacionId}/gen-R0976', [IncorporacionesController::class, 'generarR0976']);
  Route::get('/{incorporacionId}/gen-RSGC-0033', [IncorporacionesController::class, 'generarRSGC0033']);
  Route::get('/{incorporacionId}/gen-R1469', [IncorporacionesController::class, 'generarR1469']);

  //imagenes de las personas
  Route::get('/imagen-persona/{personaId}', [ImportarImagesController::class, 'getImagenFuncionario']);
  //---------------------------------------------------------------------------------
});

Route::group(['prefix' => 'file'], function () {
  Route::post('/crear-file', [FileController::class, 'crearFile']);
  Route::post('/listar-file', [FileController::class, 'listarFile']);
  Route::get('/{parentId}/children-file', [FileController::class, 'listarHijos']);
  Route::get('/{fileId}/ver-documento', [FileController::class, 'verDocumento']);
  Route::get('/{fileId}/download-documento', [FileController::class, 'downloadDocumento']);
  Route::patch('/{fileId}/dar-baja-documento', [FileController::class, 'darBajaDocumento']);
});

/* ------------------------------------------ Formacion ------------------------------------------ */
Route::group(['prefix' => 'formaciones'], function () {
  Route::put('/', [FormacionController::class, 'crearActualizarFormacion']);
  Route::get('/{personaId}/by-persona-id', [FormacionController::class, 'getByPersonaId']);
});
/* --------------------------------------- AREA FORMACION --------------------------------------- */
Route::group(['prefix' => 'areas-formacion'], function () {
  Route::get('/', [AreaFormacionController::class, 'listar']);
  Route::post('/', [AreaFormacionController::class, 'createAreaFormacion']);
  Route::post('/by-name', [AreaFormacionController::class, 'buscarOCrearAreaFormacion']);
});
/* --------------------------------------- GRADO ACADEMICO --------------------------------------- */
Route::group(['prefix' => 'grados-academico'], function () {
  Route::get('/', [GradoAcademicoController::class, 'listar']);
  Route::post('/', [GradoAcademicoController::class, 'createGradoAcademico']);
  Route::post('/by-name', [GradoAcademicoController::class, 'buscarOCrearGradoAcademico']);
});
/* --------------------------------------- INSTITUCION --------------------------------------- */
Route::group(['prefix' => 'instituciones'], function () {
  Route::get('/', [InstitucionController::class, 'listar']);
  Route::post('/', [InstitucionController::class, 'createInstitucion']);
  Route::post('/by-name', [InstitucionController::class, 'buscarOCrearInstitucion']);
});
/* ------------------------------------------- Puesto ------------------------------------------- */
Route::group(['prefix' => 'puestos'], function () {
  Route::get('/{item}/by-item', [PuestoController::class, 'getByItem']);
  Route::get('/{item}/by-item-actual', [PuestoController::class, 'getByItemActual']);
  Route::get('/{puestoId}/requisito', [PuestoController::class, 'getRequisitoPuesto']);
});
/* ------------------------------------------ Personas ------------------------------------------ */
Route::group(['prefix' => 'personas'], function () {
  Route::put('/', [PersonasController::class, 'crearActualizarPersona']);
  Route::get('/{idPersona}', [PersonasController::class, 'getById']);
  Route::get('/{ciPersona}/by-ci', [PersonasController::class, 'getByCi']);
});

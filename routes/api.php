<?php

use App\Http\Controllers\Api\GradoAcademicoController;
use App\Http\Controllers\Api\AreaFormacionController;
use App\Http\Controllers\Api\InstitucionController;
use App\Http\Controllers\Api\FormacionController;
use App\Http\Controllers\Api\PuestoController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ImportarExcelController;
use App\Http\Controllers\IncorporacionesController;
use App\Http\Controllers\PersonasController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthenticatedSessionController::class, 'store']);

Route::middleware('auth')->group(function () {
  Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

//importacion de planilla
Route::post('/planilla', [ImportarExcelController::class, 'importExcel']);

// Incorporaciones
Route::group(['prefix' => 'incorporaciones'], function () {
  Route::put('/', [IncorporacionesController::class, 'crearActualizarIncorporacion']);
  Route::post('/list', [IncorporacionesController::class, 'listPaginateIncorporaciones']);

  Route::post('/by-persona', [IncorporacionesController::class, 'getByPersona']);

  Route::get('/{incorporacionId}/gen-form-evalR0078', [IncorporacionesController::class, 'generarFormularioEvalR0078']);
  Route::get('/{incorporacionId}/gen-form-evalR1401', [IncorporacionesController::class, 'genFormEvalR1401']);
  Route::get('/{incorporacionId}/gen-form-informe-con-nota', [IncorporacionesController::class, 'genFormInformeNota']);
  Route::get('/{incorporacionId}/gen-form-informe-con-minuta', [IncorporacionesController::class, 'genFormInformeMinuta']);

  Route::get('/{incorporacionId}/gen-form-evalR0078', [IncorporacionesController::class, 'generarFormularioEvalR0078']);
  Route::get('/{incorporacionId}/gen-form-evalR1401', [IncorporacionesController::class, 'genFormEvalR1401']);
  Route::get('/{incorporacionId}/gen-form-RAP', [IncorporacionesController::class, 'genFormRAP']);
  Route::get('/{incorporacionId}/gen-form-memo', [IncorporacionesController::class, 'genFormMemo']);
  Route::get('/{incorporacionId}/gen-form-RemisionDeDocumentos', [IncorporacionesController::class, 'genFormRemisionDeDocumentos']);
  Route::get('/{incorporacionId}/gen-form-acta-de-posesion', [IncorporacionesController::class, 'genFormActaDePosesion']);
  Route::get('/{incorporacionId}/gen-form-acta-de-entrega', [IncorporacionesController::class, 'genFormActaDeEntrega']);


  Route::get('/{incorporacionId}/gen-form-informe-con-minuta', [IncorporacionesController::class, 'genFormInformeMinuta']);
  Route::get('/{incorporacionId}/gen-form-compromiso', [IncorporacionesController::class, 'genFormCompromiso']);
  Route::get('/{incorporacionId}/gen-form-declaracion-incompatibilidad', [IncorporacionesController::class, 'genFormDeclaracionIncompatibilidad']);
  Route::get('/{incorporacionId}/gen-form-etica', [IncorporacionesController::class, 'genFormEtica']);
  Route::get('/{incorporacionId}/gen-form-confidencialidad', [IncorporacionesController::class, 'genFormConfidencialidad']);

  //form cambio de item
  Route::get('/{incorporacionId}/gen-form-R-0980', [IncorporacionesController::class, 'generarR0980']);
  Route::get('/{incorporacionId}/gen-form-R-1023', [IncorporacionesController::class, 'generarR1023']);
  Route::get('/{incorporacionId}/gen-form-R-1129', [IncorporacionesController::class, 'generarR1129']);
  Route::get('/{incorporacionId}/gen-form-R-1401', [IncorporacionesController::class, 'generarR1401']);
});


/* ------------------------------------------ GradoAcademico ------------------------------------------ */
// Route::post('/grado-academico',[GradoAcademicoController::class, 'crearPersona']);
// Route::get('/GradoAcademico/buscar',[GradoAcademicoController::class, 'buscarPersona']);

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
// Route::post('/personas',[PersonasController::class, 'crearPersona']);
// Route::get('/personas/buscar',[PersonasController::class, 'buscarPersona']);

/* ------------------------------------------ Institucion ------------------------------------------ */
// Route::post('/personas',[PersonasController::class, 'crearPersona']);
// Route::get('/personas/buscar',[PersonasController::class, 'buscarPersona']);

/* ------------------------------------------- Puesto ------------------------------------------- */
Route::group(['prefix' => 'puestos'], function () {
  Route::get('/{item}/by-item', [PuestoController::class, 'getByItem']);
  Route::get('/{item}/by-item-actual', [PuestoController::class, 'getByItemActual']);
  Route::get('/{puestoId}/requisito', [PuestoController::class, 'getRequisitoPuesto']);
});
/* ------------------------------------------ Personas ------------------------------------------ */
Route::group(['prefix' => 'personas'], function () {
  Route::put('/', [PersonasController::class, 'crearActualizarPersona']);
  // Route::get('/personas/buscar',[PersonasController::class, 'buscarPersona']);
  Route::get('/{idPersona}', [PersonasController::class, 'getById']);
  Route::get('/{ciPersona}/by-ci', [PersonasController::class, 'getByCi']);
});

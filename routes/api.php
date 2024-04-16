<?php

use App\Http\Controllers\Api\AreaFormacionController;
use App\Http\Controllers\Api\FormacionController;
use App\Http\Controllers\Api\PuestoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportarExcelController;
use App\Http\Controllers\IncorporacionesController;
use App\Http\Controllers\PersonasController;
// use App\Http\Middleware\ConvertResponseFieldsToCamelCase;

Route::post('/planilla', [ImportarExcelController::class, 'importExcel']); 

// Incorporaciones
Route::group(['prefix' => 'incorporaciones'], function () {
  Route::put('/',[IncorporacionesController::class, 'crearActualizarIncorporacion']);
  Route::post('/list',[IncorporacionesController::class, 'listPaginateIncorporaciones']);
  Route::get('/{incorporacionId}/gen-form-evalR0078', [IncorporacionesController::class, 'generarFormularioEvalR0078']);
  Route::get('/{incorporacionId}/gen-form-evalR1401', [IncorporacionesController::class, 'genFormEvalR1401']);

  Route::post('/incorporaciones/{incorporacionId}/gen-form-evaluacion', [IncorporacionesController::class, 'generarFormularioEvalucaion'])->name('eval.gen-form-evaluacion');
Route::post('/incorporaciones/{incorporacionId}/gen-form-cambio-item', [IncorporacionesController::class, 'generarFormularioCambioItem'])->name('eval.gen-form-cambio-item');
Route::post('/incorporaciones/{incorporacionId}/gen-form-documentos-cambio-item', [IncorporacionesController::class, 'generarFormularioDocumentosCambioItem'])->name('eval.gen-form-documentos-cambio-item');
Route::post('/incorporaciones/{incorporacionId}/gen-form-evalR0078', [IncorporacionesController::class, 'generarFormularioEvalR0078'])->name('eval.gen-form-evalR0078');
Route::post('/incorporaciones/{incorporacionId}/gen-form-evalR1401', [IncorporacionesController::class, 'genFormEvalR1401'])->name('eval.gen-form-evalR1401');
Route::post('/incorporaciones/{incorporacionId}/gen-form-RemisionDeDocumentos', [IncorporacionesController::class, 'genFormRemisionDeDocumentos'])->name('inc.genFormRemisionDeDocumentos');
Route::post('/incorporaciones/{incorporacionId}/gen-form-RAP', [IncorporacionesController::class, 'genFormRAP'])->name('inc.genFormRAP');
Route::post('/incorporaciones/{incorporacionId}/gen-form-memo', [IncorporacionesController::class, 'genFormMemo'])->name('inc.genFormMemo');
Route::post('/incorporaciones/{incorporacionId}/gen-form-acta-de-posesion', [IncorporacionesController::class, 'genFormActaDePosesion'])->name('inc.genFormActaDePosesion');
Route::post('/incorporaciones/{incorporacionId}/gen-form-acta-de-entrega', [IncorporacionesController::class, 'genFormActaDeEntrega'])->name('inc.genFormActaDeEntrega');
Route::post('/incorporaciones/{incorporacionId}/gen-form-informe-con-nota', [IncorporacionesController::class, 'genFormInformeNota'])->name('inc.genFormInformeNota');
Route::post('/incorporaciones/{incorporacionId}/gen-form-informe-con-minuta', [IncorporacionesController::class, 'genFormInformeMinuta'])->name('inc.genFormInformeMinuta');
Route::post('/incorporaciones/{incorporacionId}/gen-form-compromiso', [IncorporacionesController::class, 'genFormCompromiso'])->name('inc.genFormCompromiso');
Route::post('/incorporaciones/{incorporacionId}/gen-form-declaracion-incompatibilidad', [IncorporacionesController::class, 'genFormDeclaracionIncompatibilidad'])->name('inc.genFormDeclaracionIncompatibilidad');
Route::post('/incorporaciones/{incorporacionId}/gen-form-etica', [IncorporacionesController::class, 'genFormEtica'])->name('inc.genFormEtica');
Route::post('/incorporaciones/{incorporacionId}/gen-form-confidencialidad', [IncorporacionesController::class, 'genFormConfidencialidad'])->name('inc.genFormConfidencialidad');



});


/* ------------------------------------------ GradoAcademico ------------------------------------------ */
// Route::post('/grado-academico',[GradoAcademicoController::class, 'crearPersona']);
// Route::get('/GradoAcademico/buscar',[GradoAcademicoController::class, 'buscarPersona']);

/* ------------------------------------------ Formacion ------------------------------------------ */
Route::group(['prefix' => 'formaciones'], function () {
  Route::put('/',[FormacionController::class,'crearActualizarFormacion']);
  Route::get('/{personaId}/by-persona-id',[FormacionController::class,'getByPersonaId']);
});
/* --------------------------------------- AREA FORMACION --------------------------------------- */
Route::group(['prefix' => 'areas-formacion'], function () {
  Route::get('/',[AreaFormacionController::class,'listar']);
  Route::post('/',[AreaFormacionController::class,'createAreaFormacion']);
  Route::post('/by-name',[AreaFormacionController::class,'buscarOCrearAreaFormacion']);
});
// Route::post('/personas',[PersonasController::class, 'crearPersona']);
// Route::get('/personas/buscar',[PersonasController::class, 'buscarPersona']);

/* ------------------------------------------ Institucion ------------------------------------------ */
// Route::post('/personas',[PersonasController::class, 'crearPersona']);
// Route::get('/personas/buscar',[PersonasController::class, 'buscarPersona']);

/* ------------------------------------------- Puesto ------------------------------------------- */
Route::group(['prefix' => 'puestos'], function () {
  Route::get('/{item}/by-item',[PuestoController::class,'getByItem']);
  Route::get('/{puestoId}/requisito',[PuestoController::class,'getRequisitoPuesto']);
});
/* ------------------------------------------ Personas ------------------------------------------ */
Route::group(['prefix' => 'personas'], function () {
  Route::put('/',[PersonasController::class, 'crearActualizarPersona']);
  // Route::get('/personas/buscar',[PersonasController::class, 'buscarPersona']);
  Route::get('/{idPersona}',[PersonasController::class,'getById']);
  Route::get('/{ciPersona}/by-ci',[PersonasController::class,'getByCi']);
});

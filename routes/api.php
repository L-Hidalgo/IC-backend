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
  Route::post('/{incorporacionId}/gen-form-evalR0078', [IncorporacionesController::class, 'generarFormularioEvalR0078']);
  Route::post('/{incorporacionId}/gen-form-evalR1401', [IncorporacionesController::class, 'genFormEvalR1401']);


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

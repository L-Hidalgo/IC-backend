<?php

use App\Http\Controllers\Api\PuestoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportarExcelController;
use App\Http\Controllers\IncorporacionesController;
use App\Http\Controllers\PersonasController;
use App\Http\Middleware\ConvertResponseFieldsToCamelCase;

Route::post('/planilla', [ImportarExcelController::class, 'importExcel']); 

// Incorporaciones
Route::group(['prefix' => 'incorporaciones'], function () {
  Route::put('/',[IncorporacionesController::class, 'crearActualizarIncorporacion']);
});


/* ------------------------------------------ GradoAcademico ------------------------------------------ */
// Route::post('/grado-academico',[GradoAcademicoController::class, 'crearPersona']);
// Route::get('/GradoAcademico/buscar',[GradoAcademicoController::class, 'buscarPersona']);

/* ------------------------------------------ Formacion ------------------------------------------ */
// Route::post('/personas',[PersonasController::class, 'crearPersona']);
// Route::get('/personas/buscar',[PersonasController::class, 'buscarPersona']);

/* ------------------------------------------ Institucion ------------------------------------------ */
// Route::post('/personas',[PersonasController::class, 'crearPersona']);
// Route::get('/personas/buscar',[PersonasController::class, 'buscarPersona']);

/* ------------------------------------------- Puesto ------------------------------------------- */
Route::get('/puestos/{item}/by-item',[PuestoController::class,'getByItem']);

/* ------------------------------------------ Personas ------------------------------------------ */
Route::group(['prefix' => 'personas'], function () {
  Route::put('/',[PersonasController::class, 'crearActualizarPersona']);
  // Route::get('/personas/buscar',[PersonasController::class, 'buscarPersona']);
  Route::get('/{idPersona}',[PersonasController::class,'getById']);
  Route::get('/{ciPersona}/by-ci',[PersonasController::class,'getByCi']);
});

<?php

use App\Http\Controllers\Api\PuestoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportarExcelController;
use App\Http\Controllers\IncorporacionesController;
use App\Http\Controllers\PersonasController;
use App\Http\Middleware\ConvertResponseFieldsToCamelCase;

Route::post('/planilla', [ImportarExcelController::class, 'importExcel']); 

// Incorporaciones
Route::put('/incorporacion',[IncorporacionesController::class, 'crearActualizarIncorporacion']);

/* ------------------------------------------ Personas ------------------------------------------ */
Route::post('/personas',[PersonasController::class, 'crearPersona']);
// Route::get('/personas/buscar',[PersonasController::class, 'buscarPersona']);

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

/*------------------------------------------buscando a la <persona----------------------------></persona---------------------------->*/
Route::get('/puestos/{ciPersona}/by-ci',[PersonasController::class,'getByCi']);
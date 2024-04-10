<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportarExcelController;
use App\Http\Controllers\IncorporacionesController;
use App\Http\Controllers\PersonasController;

Route::post('/planilla', [ImportarExcelController::class, 'importExcel']); 

// Incorporaciones
Route::put('/incorporacion',[IncorporacionesController::class, 'crearActualizarIncorporacion']);

/* ------------------------------------------ Personas ------------------------------------------ */
Route::post('/personas',[PersonasController::class, 'crearPersona']);
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportarExcelController;

Route::post('/planilla', [ImportarExcelController::class, 'importExcel']); 
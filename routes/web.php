<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportarExcelController;


/*Route::get('/', function () {
    return view('welcome');
});*/
 
Route::post('/planilla', [ImportarExcelController::class, 'importExcel']); 
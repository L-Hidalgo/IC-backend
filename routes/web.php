<?php
use App\Http\Controllers\ImportarExcelController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ImportarImagesController;
use App\Http\Controllers\IncorporacionesController;
use App\Models\Gerencia;

Route::get('/', function () {
    return view('welcome');
});
Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
    $startDate = Carbon::now()->startOfWeek();
    $endDate = Carbon::now()->endOfWeek();
    $puestos = Puesto::count();
    $puestosAcefalia = Puesto::where('estado', 'ACEFALIA')->count();
    $incorporaciones = Incorporacion::whereNotNull('puesto_nuevo_id')
      ->whereNull('puesto_actual_id')
      ->whereBetween('created_at', [$startDate, $endDate])
      ->count();
    $cambioItem = Incorporacion::whereNotNull('puesto_actual_id')
      ->whereNotNull('puesto_nuevo_id')
      ->whereBetween('created_at', [$startDate, $endDate])
      ->count();
    $incorporacionesFebrero = Incorporacion::whereNotNull('puesto_nuevo_id')
      ->whereNull('puesto_actual_id')
      ->whereMonth('created_at', 2)
      ->whereYear('created_at', date('Y'))
      ->count();
  
    $cambioItemFebrero = Incorporacion::whereNotNull('puesto_actual_id')
      ->whereNotNull('puesto_nuevo_id')
      ->whereMonth('created_at', 2)
      ->whereYear('created_at', date('Y'))
      ->count();
    $incorporacionesMarzo = Incorporacion::whereNotNull('puesto_nuevo_id')
      ->whereNull('puesto_actual_id')
      ->whereMonth('created_at', 3)
      ->whereYear('created_at', date('Y'))
      ->count();
  
    $cambioItemMarzo = Incorporacion::whereNotNull('puesto_actual_id')
      ->whereNotNull('puesto_nuevo_id')
      ->whereMonth('created_at', 3)
      ->whereYear('created_at', date('Y'))
      ->count();
    $incorporacionesAbril = Incorporacion::whereNotNull('puesto_nuevo_id')
      ->whereNull('puesto_actual_id')
      ->whereMonth('created_at', 4)
      ->whereYear('created_at', date('Y'))
      ->count();
  
    $cambioItemAbril = Incorporacion::whereNotNull('puesto_actual_id')
      ->whereNotNull('puesto_nuevo_id')
      ->whereMonth('created_at', 4)
      ->whereYear('created_at', date('Y'))
      ->count();
    return Inertia::render('Dashboard', ['puestos' => $puestos, 'incorporaciones' => $incorporaciones, 'cambioItem' => $cambioItem, 'puestosAcefalia' => $puestosAcefalia, 'incorporacionesFebrero' => $incorporacionesFebrero, 'cambioItemFebrero' => $cambioItemFebrero, 'incorporaciones Marzo' => $incorporacionesMarzo, 'cambioItemMarzo' => $cambioItemMarzo, 'incorporacionesAbril' => $incorporacionesAbril, 'cambioItemAbril' => $cambioItemAbril]);
  })->name('dashboard');
  
  Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/users', [ViewController::class, 'users'])->name('users');
    Route::get('/roles', [ViewController::class, 'roles'])->name('roles');
    Route::get('/permissions', [ViewController::class, 'permissions'])->name('permissions');
    Route::get('/imagen-persona/{personaId}', [ImportarImagesController::class, 'getImagenPersona'])->name('imagen-persona');
    Route::get('/download-form/{fileName}', [IncorporacionesController::class, 'downloadEvalForm'])->name('download.form')->middleware('auth');
  });
  
  Route::middleware(['auth:sanctum', 'verified'])->get('/migraciones', function () {
    return Inertia::render('Migraciones/Index', [
      'gerencias' => Gerencia::select('id', 'nombre')->get()->map(function ($gerencia) {
        return [
          'key' => 'g-' . $gerencia->id,
          'icon' => 'pi pi-building',
          'label' => $gerencia->nombre,
          'children' => $gerencia->departamento->map(function ($dep) {
            return [
              'key' => 'd-' . $dep->id,
              'icon' => 'pi pi-sitemap',
              'label' => $dep->nombre
            ];
          })
        ];
      })
    ]);
  })->name('migraciones');
  
  Route::middleware(['auth:sanctum', 'verified'])->get('/incorporaciones', function () {
    return Inertia::render('Incorporaciones/Index');
  })->name('incorporaciones');

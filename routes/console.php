<?php

use App\Models\Interinato;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


// Schedule para mover todos los interinatos del dia a destino cada dia a las 1 y 57
Schedule::call(function () {
  $interinatosADestino = Interinato::where('estado', 0)->where('fch_inicio_interinato', '<=', Carbon::now()->toDateString())->get();
  foreach ($interinatosADestino as $interinato) {
    $interinato->actualizarInterinatoDestino();
  }
  Log::info('Interinatos migrados a destino' . sizeof($interinatosADestino));
})->daily()->at('05:00');

// Schedule para mover todos los interinatos de su puesto de destino al de origen
Schedule::call(function () {
  $interinatosAOrigen = Interinato::where('estado', 1)->where('fch_fin_interinato', '<=', Carbon::now()->toDateString())->get();
  foreach ($interinatosAOrigen as $interinato) {
    $interinato->actualizarInterinatoOrigen();
  }
  Log::info('Interinatos migrados a origen' . sizeof($interinatosAOrigen));
})->daily()->at('23:00');

Schedule::call(function () {
  //Print format fecha hora server
  Log::info('Schedule example running <------------------'.Carbon::now()->toDateTimeString());
})->everyMinute();
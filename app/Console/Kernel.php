<?php

namespace App\Console;

use App\Models\Interinato;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
  /**
   * Define the application's command schedule.
   *
   * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
   * @return void
   */
  protected function schedule(Schedule $schedule)
  {
    Log::info('schedule running ------------------------->');
    // Schedule para mover todos los interinatos del dia a destino
    $schedule->call(function () {
      $interinatosADestino = Interinato::where('estado', 0)->where('fch_inicio_interinato', '<=', Carbon::now()->toDateString())->get();
      foreach ($interinatosADestino as $interinato) {
        $interinato->actualizarInterinatoDestino();
      }
      Log::info('Interinatos migrados a destino' . sizeof($interinatosADestino));
    })->daily()->at('05:00');

    // Schedule para mover todos los interinatos de su puesto de destino al de origen
    $schedule->call(function () {
      $interinatosAOrigen = Interinato::where('estado', 1)->where('fch_fin_interinato', '<=', Carbon::now()->toDateString())->get();
      foreach ($interinatosAOrigen as $interinato) {
        $interinato->actualizarInterinatoOrigen();
      }
      Log::info('Interinatos migrados a origen' . sizeof($interinatosAOrigen));
    })->daily()->at('23:00');

    $schedule->call(function () {
      Log::info('Tarea programada ejecutada. <------------------');
    })->everyMinute();
  }

  /**
   * Register the commands for the application.
   *
   * @return void
   */
  protected function commands()
  {
    $this->load(__DIR__ . '/Commands');

    require base_path('routes/console.php');
  }
}

<?php

date_default_timezone_set('America/La_Paz');

use App\Http\Middleware\ConvertResponseFieldsToCamelCase;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
      $middleware->api(append: [
        ConvertResponseFieldsToCamelCase::class
      ]);
        // $middleware->append(ConvertResponseFieldsToCamelCase::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

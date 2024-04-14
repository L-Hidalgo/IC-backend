<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ConvertResponseFieldsToCamelCase
{
    // public function handle($request, Closure $next)
    // {
    //     $response = $next($request);
    //     Log::info($response);
    //     $content = $response->getContent();

    //     try {
    //         $json = json_decode($content, true);
    //         $replaced = [];

    //         foreach ($json as $key => $value) {
    //             $replaced[Str::camel($key)] = $value;
    //         }

    //         $response->setContent(json_encode($replaced));
    //     } catch (\Exception $e) {
    //         // Handle exceptions here
    //     }

    //     return $response;
    // }

    public function handle($request, Closure $next)
{
    $response = $next($request);

    if ($response instanceof \Illuminate\Http\JsonResponse) {
        $data = $response->getData();

        $replaced = [];
        foreach ($data as $key => $value) {
            $replaced[Str::camel($key)] = is_array($value) || is_object($value) ? ConvertResponseFieldsToCamelCase::convert_to_camel_case($value) : $value;
        }

        $response->setData($replaced);
    }

    return $response;
}

    function convert_to_camel_case($data)
{
    if (is_array($data)) {
        $replaced = [];
        foreach ($data as $key => $value) {
            $replaced[Str::camel($key)] = ConvertResponseFieldsToCamelCase::convert_to_camel_case($value);
        }
        return $replaced;
    } elseif (is_object($data)) {
        $replaced = [];
        foreach ($data as $key => $value) {
            $replaced[Str::camel($key)] = ConvertResponseFieldsToCamelCase::convert_to_camel_case($value);
        }
        return $replaced;
    } else {
        return $data;
    }
}
}
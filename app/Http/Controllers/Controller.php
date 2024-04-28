<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{

    public function sendError($data = [], $http_code_response = 404){
        return response()->json([
            'success' => false,
            'message' => $data,
            'data'  => []
        ], $http_code_response);
    }

    public function sendObject($data,$message = 'Operacion realizada exitosamente', $http_code_response = 200) {
      return response()->json([
        'objeto'      => $data,
        'message'   => $message,
        'transsacton' => true,
      ] ,$http_code_response);
    }

    public function sendList($list,$message = 'Operacion realizada exitosamente', $http_code_response = 200) {
      return response()->json([
        'objetosList'   => $list,
        'message'       => $message,
        'transsacton'   => true,
      ] ,$http_code_response);
    }

    public function sendPaginated($paginatedModel,$message = 'Operacion realizada exitosamente', $http_code_response = 200) {
      return response()->json([
        'objetosList'      => $paginatedModel->getCollection(),
        'page' => $paginatedModel->currentPage(),
        'total' => $paginatedModel->total(),
        'limit' => $paginatedModel->perPage(),
        'message'   => $message,
        'transsacton' => true,
      ] ,$http_code_response);
    }

    public function sendSuccess($data = [], $http_code_response = 200)
    {
        if(getType($data) == 'string'){
            return response()->json([
                'success'   => true,
                'message'   => $data,
                'data'      => $data
            ] ,$http_code_response);
        }

        return response()->json([
            'success'   => true,
            'message'    => "Success",
            'data'      => $data,
        ] ,$http_code_response);
    }
}

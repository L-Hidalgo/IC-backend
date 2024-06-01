<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rol;

class RolController extends Controller
{
    public function listar()
    {
        $rol = Rol::select(['name'])->get();
        return $this->sendList($rol);
    }

}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rol;

class RolController extends Controller
{
    public function listarRol()
    {
        $rol = Rol::select(['id', 'name'])->get();
        return $this->sendList($rol);
    }

}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use App\Models\User;

class RolController extends Controller
{
    public function listarRol()
    {
        $rol = Rol::select(['id', 'name'])->get();
        return $this->sendList($rol);
    }

    public function listarUserRol($userId){
        $usuario = User::find($userId);
        
        if($usuario){
            $rolesUsuario = $usuario->roles()->select('id', 'name')->get();
            
            $rolesUsuario->makeHidden('pivot');
            
            return response()->json(['roles' => $rolesUsuario], 200);
        } else {
            return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
        }
    }
}

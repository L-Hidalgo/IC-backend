<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AreaFormacion;
use Illuminate\Http\Request;

class AreaFormacionController extends Controller
{
    public function listar()
    {
        $areasFormaci_personaon = AreaFormacion::select(['id_area_formacion', 'nombre_area_formacion'])->get();
        return $this->sendList($areasFormaci_personaon);
    }

    public function createAreaFormacion(Request $request)
    {
        $validatedData = $request->validate([
            'nombreAreaFormacion' => 'required|string',
        ]);
        $areaFormacion = new AreaFormacion();
        $areaFormacion->nombre_area_formacion = $validatedData['nombreAreaFormacion'];
        $areaFormacion->save();
        return $this->sendObject($areaFormacion);
    }


    public function buscarOCrearAreaFormacion(Request $request)
    {
        $validatedData = $request->validate([
            'nombreAreaFormacion' => 'required|string',
        ]);
        $areaFormacion = AreaFormacion::where('nombre_area_formacion', $validatedData['nombreAreaFormacion'])->first();
        if(!isset($areaFormacion)) {
          $areaFormacion = new AreaFormacion();
          $areaFormacion->nombre_area_formacion = $validatedData['nombreAreaFormacion'];
          $areaFormacion->save();
        }
        return $this->sendObject($areaFormacion);
    }
    
}

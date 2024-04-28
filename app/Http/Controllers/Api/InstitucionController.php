<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Institucion;
use Illuminate\Http\Request;

class InstitucionController extends Controller
{
    public function listar()
    {
        $institucion = Institucion::select(['id_institucion', 'nombre_institucion'])->get();
        return $this->sendList($institucion);
    }

    public function createInstitucion(Request $request)
    {
        $validatedData = $request->validate([
            'nombreInstitucion' => 'required|string',
        ]);

        $existingInstitucion= Institucion::where('nombre_institucion', $validatedData['nombreInstitucion'])->first();
        if($existingInstitucion){
            return response()->json(['error'=>' Ya existe una institucion con ese nombre.'], 200);
        }

        $institucion = new Institucion();
        $institucion->nombre_institucion = $validatedData['nombreInstitucion'];
        $institucion->save();
        return $this->sendObject($institucion);
    }


    public function buscarOCrearInstitucion(Request $request)
    {
        $validatedData = $request->validate([
            'nombreInstitucion' => 'required|string',
        ]);
        $institucion = Institucion::where('nombre_institucion', $validatedData['nombreInstitucion'])->first();
        if(!isset($institucion)) {
          $institucion = new Institucion();
          $institucion->nombre_institucion = $validatedData['nombreInstitucion'];
          $institucion->save();
        }
        return $this->sendObject($institucion);
    }
    
}

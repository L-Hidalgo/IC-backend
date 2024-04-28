<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GradoAcademico;
use Illuminate\Http\Request;

class GradoAcademicoController extends Controller
{
    public function listar()
    {
        $gradosAcademicos = GradoAcademico::select(['id_grado_academico', 'nombre_grado_academico'])->get();
        return $this->sendList($gradosAcademicos);
    }

    public function createGradoAcademico(Request $request)
    {
        $validatedData = $request->validate([
            'nombreGradoAcademico' => 'required|string',
        ]);

        $existingGradoAcademico = GradoAcademico::where('nombre_grado_academico', $validatedData['nombreGradoAcademico'])->first();
        if($existingGradoAcademico){
            return response()->json(['error'=>'Ya existe grado academico con este nombre.'], 422);
        }

        $gradoAcademico = new GradoAcademico();
        $gradoAcademico->nombre_grado_academico = $validatedData['nombreGradoAcademico'];
        $gradoAcademico->save();
        return $this->sendObject($gradoAcademico);
    }

    public function buscarOCrearGradoAcademico(Request $request)
    {
        $validatedData = $request->validate([
            'nombreGradoAcademico' => 'required|string',
        ]);
        $gradoAcademico = GradoAcademico::where('nombre_grado_academico', $validatedData['nombreGradoAcademico'])->first();
        if(!isset($gradoAcademico)) {
          $gradoAcademico = new GradoAcademico();
          $gradoAcademico->nombre_grado_academico = $validatedData['nombreGradoAcademico'];
          $gradoAcademico->save();
        }
        return $this->sendObject($gradoAcademico);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Formacion;
use Illuminate\Http\Request;

class FormacionController extends Controller
{
    public function getByPersonaId($personaId) {
        $formaciones = Formacion::with(['institucion', 'gradoAcademico', 'areaFormacion:id_area_formacion,nombre_area_formacion'])->where('persona_id', $personaId)->first();
        return $this->sendObject($formaciones);   
    }

    public function crearActualizarFormacion(Request $request)
    {
        $validatedData = $request->validate([
          'idFormacion' => 'nullable|integer',
          'personaId' => 'integer',
          'institucionId' => 'nullable|integer',
          'gradoAcademicoId' => 'nullable|integer',
          'areaFormacionId' => 'nullable|integer',
          'gestionFormacion' => 'nullable|string',
        ]);
        if($validatedData['idFormacion']) {
          $formacion = Formacion::find($validatedData['idFormacion']);
        } else {
          $formacion = new Formacion();
        }
    
        // agregar campos para actualizacion
        $formacion->persona_id = $validatedData['personaId'];
        $formacion->institucion_id = $validatedData['institucionId'];
        $formacion->grado_academico_id = $validatedData['gradoAcademicoId'];
        $formacion->area_formacion_id = $validatedData['areaFormacionId'];
        if(!empty($validatedData['gestionFormacion'])) {
            $year = intval($validatedData['gestionFormacion']);
            $date = \Carbon\Carbon::create($year, 1, 1, 0, 0, 0);
            $formacion->gestion_formacion = $date;
        }
        
        // guardar
        $formacion->save();
        return $this->sendSuccess($formacion);
    }
}

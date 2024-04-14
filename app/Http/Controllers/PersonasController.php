<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use Illuminate\Http\Request;

class PersonasController extends Controller
{

  public function crearPersona(Request $request)
  {
    $validatedData = $request->validate([
      'ci_persona' => 'string',
      'primer_apellido_persona' => 'string',
      'segundo_apellido_persona' => 'string',
      'nombre_persona' => 'string',
    ]);
    // dd($validatedData);

    $persona = Persona::create([]);
    // agregar campos para actualizacion
    $persona->ci_persona = $validatedData['ci_persona'];
    $persona->primer_apellido_persona = $validatedData['primer_apellido_persona'];
    $persona->segundo_apellido_persona = $validatedData['segundo_apellido_persona'];
    $persona->nombre_persona = $validatedData['nombre_persona'];
    // guardar
    $persona->save();
    return $this->sendSuccess($persona);
  }

  public function getByCi($ci_persona)
  {
    $persona = Persona::where('ci_persona', $ci_persona)->first();

    if (!$persona) {
      return response()->json(['message' => 'Persona no encontrada'], 404);
    }

    return response()->json($persona, 200);
  }


}
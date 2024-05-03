<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use Illuminate\Http\Request;

class PersonasController extends Controller
{

  public function crearActualizarPersona(Request $request)
  {
    $validatedData = $request->validate([
      'idPersona' => 'nullable|integer',
      'ciPersona' => 'string',
      'expPersona' => 'nullable|string',
      'nombrePersona' => 'string',
      'primerApellidoPersona' => 'nullable|string',
      'segundoApellidoPersona' => 'nullable|string',
      'generoPersona' => 'nullable|string',
      'fchNacimientoPersona' => 'nullable|string',
    ]);
    // dd($validatedData);

    if($validatedData['idPersona']) {
      $persona = Persona::find($validatedData['idPersona']);
    } else {
      $persona = new Persona();
    }

    // agregar campos para actualizacion
    $persona->ci_persona = $validatedData['ciPersona'];
    $persona->primer_apellido_persona = $validatedData['primerApellidoPersona'];
    $persona->segundo_apellido_persona = $validatedData['segundoApellidoPersona'];
    $persona->nombre_persona = $validatedData['nombrePersona'];
    $persona->exp_persona = $validatedData['expPersona'];
    $persona->genero_persona = $validatedData['generoPersona'];
    $persona->fch_nacimiento_persona = $validatedData['fchNacimientoPersona'];
    
    // guardar
    $persona->save();
    return $this->sendObject($persona);
  }

  public function getByCi($ci_persona)
  {
    $persona = Persona::where('ci_persona', $ci_persona)->first();

    if (!$persona) {
      return $this->sendObject(null,'No se encontro la persona', 404);
    }
    return $this->sendObject($persona);
  }

  public function getById($id_persona)
  {
    $persona = Persona::find($id_persona);

    if (!$persona) {
      return $this->sendObject(null,'No se encontro la persona', 404);
    }
    return $this->sendObject($persona);
  }


}
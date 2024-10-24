<?php

namespace App\Http\Controllers;

use App\Models\Imagen;
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
      'imagenPersona' => 'nullable|string',
    ]);

    $persona = Persona::find($validatedData['idPersona']) ?? new Persona();
    $persona->ci_persona = $validatedData['ciPersona'];
    $persona->primer_apellido_persona = $validatedData['primerApellidoPersona'];
    $persona->segundo_apellido_persona = $validatedData['segundoApellidoPersona'];
    $persona->nombre_persona = $validatedData['nombrePersona'];
    $persona->exp_persona = $validatedData['expPersona'];
    $persona->genero_persona = $validatedData['generoPersona'];
    $persona->fch_nacimiento_persona = $validatedData['fchNacimientoPersona'];

    $persona->save();

    if (isset($validatedData['imagenPersona'])) {
      list($type, $imageData) = explode(';', $validatedData['imagenPersona']);
      $type = str_replace('data:', '', $type);
      $type = str_replace('base64,', '', $type);

      if (strpos($imageData, 'base64,') !== false) {
        $imageData = explode('base64,', $imageData)[1];
      }

      $imagen = new Imagen();
      $imagen->base64_imagen = $imageData;
      $imagen->tipo_mime_imagen = $type;
      $imagen->persona_id = $persona->id_persona;

      $imagen->save();
    }

    return $this->sendObject($persona);
  }

  public function getByCi($ci_persona)
  {
    $persona = Persona::where('ci_persona', $ci_persona)->first();

    if (!$persona) {
      return $this->sendObject(null, 'No se encontro la persona', 404);
    }
    return $this->sendObject($persona);
  }

  public function getById($id_persona)
  {
    $persona = Persona::find($id_persona);

    if (!$persona) {
      return $this->sendObject(null, 'No se encontro la persona', 404);
    }
    return $this->sendObject($persona);
  }
}

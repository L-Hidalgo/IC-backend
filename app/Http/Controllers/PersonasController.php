<?php

namespace App\Http\Controllers;

use App\Models\Imagen;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PersonasController extends Controller
{

  public function crearActualizarPersona(Request $request)
  {
    $validatedData = $request->validate([
      'idPersona' => 'nullable|integer',
      'ciPersona' => 'required|string',
      'expPersona' => 'nullable|string',
      'nombrePersona' => 'required|string',
      'primerApellidoPersona' => 'nullable|string',
      'segundoApellidoPersona' => 'nullable|string',
      'generoPersona' => 'nullable|string',
      'fchNacimientoPersona' => 'nullable|date',
      'file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Tamaño máximo de 2MB
    ]);
    Log::info('Datos recibidos:', $request->all());

    // Verifica si la persona ya existe o crea una nueva
    $persona = $validatedData['idPersona'] ? Persona::find($validatedData['idPersona']) : new Persona();

    // Asigna los valores al modelo de Persona
    $persona->ci_persona = $validatedData['ciPersona'];
    $persona->primer_apellido_persona = $validatedData['primerApellidoPersona'];
    $persona->segundo_apellido_persona = $validatedData['segundoApellidoPersona'];
    $persona->nombre_persona = $validatedData['nombrePersona'];
    $persona->exp_persona = $validatedData['expPersona'];
    $persona->genero_persona = $validatedData['generoPersona'];
    $persona->fch_nacimiento_persona = $validatedData['fchNacimientoPersona'];

    // Guarda o actualiza la persona
    $persona->save();

    // Manejo de la imagen
    if ($request->hasFile('file')) {
      try {
        // Obtener el archivo de la imagen
        $file = $request->file('file');

        // Leer el archivo y convertirlo a base64
        $base64Image = base64_encode(file_get_contents($file->getRealPath()));

        // Crear y guardar la imagen en la tabla dde_imagenes
        $imagen = new Imagen();
        $imagen->base64_imagen = $base64Image; // Guardar el contenido en base64
        $imagen->tipo_mime_imagen = $file->getClientMimeType(); // Guardar el tipo MIME
        $imagen->persona_id = $persona->id_persona; // Asociar a la persona
        $imagen->save();
      } catch (\Exception $e) {
        return response()->json(['error' => 'Error al guardar la imagen: ' . $e->getMessage()], 500);
      }
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

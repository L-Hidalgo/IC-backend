<?php

namespace App\Http\Controllers;

use App\Models\Imagen;
use Illuminate\Http\Request;
use App\Models\Persona;
use ZipArchive;

class ImportarImagesController extends Controller
{
    public function importImagenes(Request $request)
    {
        try {
            if ($request->hasFile('file')) {
                $archivo = $request->file('file');
                $nombreArchivo = $archivo->getClientOriginalName();
                $directorioDestino = public_path('imagenes_personas');

                $archivo->move($directorioDestino, $nombreArchivo);

                $rutaArchivo = $directorioDestino . '/' . $nombreArchivo;

                if (pathinfo($rutaArchivo, PATHINFO_EXTENSION) === 'zip') {
                    $zip = new ZipArchive;
                    if ($zip->open($rutaArchivo) === true) {
                        for ($i = 0; $i < $zip->numFiles; $i++) {
                            $nombreArchivo = $zip->getNameIndex($i);
                            $partesNombre = pathinfo($nombreArchivo);
                            $ci_persona = $partesNombre['filename'];
                            $extension = isset($partesNombre['extension']) ? $partesNombre['extension'] : 'txt'; 

                            $persona = Persona::where('ci_persona', $ci_persona)->first();

                            if ($persona) {
                                if ($persona->id_persona !== null) {
                                    $imagen = new Imagen();
                                    $imagen->persona_id = $persona->id_persona;
                                    $imagenData = $zip->getFromName($nombreArchivo);
                                    $imagenData = base64_encode($imagenData);
                                    $imagen->base64_imagen = $imagenData;
                                    $imagen->tipo_mime_imagen = $extension;
                                    $imagen->save();
                                } else {
                                    return response()->json(["El ID de persona no es válido para CI: $ci_persona"], 500);
                                }
                            }
                        }
                        $zip->close();
                    } else {
                        return response()->json(['error' => 'No se pudo abrir el archivo ZIP.'], 500);
                    }
                    unlink($rutaArchivo);

                    return response()->json(['message' => 'Imágenes importadas correctamente.']);
                } else {
                    return response()->json(['error' => 'El archivo no es un archivo ZIP.'], 400);
                }
            } else {
                return response()->json(['error' => 'No se ha proporcionado un archivo.'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function getImagenFuncionario($personaId)
    {
        $persona = Persona::find($personaId);

        if ($persona) {
            $imagen = $persona->imagenes()->latest()->first();

            if ($imagen) {
                $base64_imagen = $imagen->base64_imagen;
                $tipo_mime_imagen = $imagen->tipo_mime_imagen;
                $imagen_data = base64_decode($base64_imagen);

                return response($imagen_data)
                    ->header('Content-Type', 'image/jpeg')
                    ->header('Content-Disposition', 'inline')
                    ->header('Content-Length', strlen($imagen_data));
            } else {
                $imagenPorDefecto = public_path('img/user.png');
                $imagen_data = file_get_contents($imagenPorDefecto);
                $tipo_mime_imagen = mime_content_type($imagenPorDefecto);

                return response($imagen_data)
                    ->header('Content-Type', $tipo_mime_imagen)
                    ->header('Content-Disposition', 'inline')
                    ->header('Content-Length', filesize($imagenPorDefecto));
            }
        } else {
            return response()->json(['message' => 'No se encontró a la persona.'], 404);
        }
    }

    public function getImagenUserPersona($personaCi)
    {
        $persona = Persona::where('ci_persona', $personaCi)->first();

        if ($persona) {
            $imagen = $persona->imagenes()->latest()->first();

            if ($imagen) {
                $base64_imagen = $imagen->base64_imagen;
                $tipo_mime_imagen = $imagen->tipo_mime_imagen;
                $imagen_data = base64_decode($base64_imagen);

                return response($imagen_data)
                    ->header('Content-Type', 'image/jpeg')
                    ->header('Content-Disposition', 'inline')
                    ->header('Content-Length', strlen($imagen_data));
            } else {
                $imagenPorDefecto = public_path('img/user.png');
                $imagen_data = file_get_contents($imagenPorDefecto);
                $tipo_mime_imagen = mime_content_type($imagenPorDefecto);

                return response($imagen_data)
                    ->header('Content-Type', $tipo_mime_imagen)
                    ->header('Content-Disposition', 'inline')
                    ->header('Content-Length', filesize($imagenPorDefecto));
            }
        } else {
            return response()->json(['message' => 'No se encontró a la persona.'], 404);
        }
    }
}

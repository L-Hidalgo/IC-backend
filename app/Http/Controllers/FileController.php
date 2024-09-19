<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function crearFile(Request $request)
    {
        $request->validate([
            'nombreFile' => 'required|string|max:255',
            'tipoDocumentoFile' => 'required|integer',
            'tipoFile' => 'required|integer',
            'createdByFile' => 'required|integer',
            'file' => 'nullable|file',
            'files' => 'nullable|array',
            'files.*' => 'file',
        ]);
    
        $nombreFile = $request->input('nombreFile');
        $tipoDocumentoFile = $request->input('tipoDocumentoFile');
        $tipoFile = $request->input('tipoFile');
        $createdByFile = $request->input('createdByFile');
        $dateFolder = date('d-m-Y');

        if ($tipoFile == 1) {
            if ($request->hasFile('files')) {
                if ($tipoDocumentoFile == 1) {
                    $storagePath = "scanned_documents/file/{$dateFolder}/{$nombreFile}";
                } elseif ($tipoDocumentoFile == 2) {
                    $storagePath = "scanned_documents/mem-rap/{$dateFolder}/{$nombreFile}";
                } else {
                    return response()->json(['message' => 'Tipo de documento no válido.'], 400);
                }
                Storage::makeDirectory($storagePath);

                $files = $request->file('files');

                foreach ($files as $file) {
                    $fileNameWithExtension = $file->getClientOriginalName();
                    $fileName = pathinfo($fileNameWithExtension, PATHINFO_FILENAME);

                    if (is_numeric($fileName)) {
                        $persona = Persona::where('ci_persona', $fileName)->first();
                    } else {
                        $persona = Persona::whereRaw('CONCAT(nombre_persona, " ", primer_apellido_persona, " ", segundo_apellido_persona) LIKE ?', ['%' . $fileName . '%'])->first();
                    }

                    $filePath = "{$storagePath}/{$fileNameWithExtension}";
                    $file->storeAs($storagePath, $fileNameWithExtension);

                    $carpeta = File::create([
                        'path' => $filePath,
                        'persona_id' => $persona ? $persona->id_persona : null,
                        'nombre_file' => $fileNameWithExtension,
                        'tipo_documento_file' => $tipoDocumentoFile,
                        'tipo_file' => $tipoFile,
                        'created_by_file' => $createdByFile,
                        'estado_file' => 1,
                    ]);

                    return response()->json([
                        'message' => 'Carpeta y archivos creados exitosamente.',
                        'CARPETA' => $carpeta
                    ]);
                }
                
            } else {
                if ($tipoDocumentoFile == 1) {
                    $storagePath = "scanned_documents/file/{$dateFolder}/{$nombreFile}";
                } elseif ($tipoDocumentoFile == 2) {
                    $storagePath = "scanned_documents/mem-rap/{$dateFolder}/{$nombreFile}";
                } else {
                    return response()->json(['message' => 'Tipo de documento no válido.'], 400);
                }

                if (Storage::exists($storagePath)) {
                    return response()->json(['message' => 'La carpeta ya existe.'], 400);
                }

                Storage::makeDirectory($storagePath);

                $carpeta = File::create([
                    'nombre_file' => $nombreFile,
                    'tipo_documento_file' => $tipoDocumentoFile,
                    'tipo_file' => $tipoFile,
                    'created_by_file' => $createdByFile,
                    'estado_file' => 1,
                ]);

                return response()->json([
                    'message' => 'Se creó la carpeta exitosamente.',
                    'CARPETA' => $carpeta
                ]);
            }
        } else {
            
        }
    }
}

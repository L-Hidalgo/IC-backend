<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use ZipArchive;

class FileController extends Controller
{
    public function crearFile(Request $request)
    {
        $request->validate([
            'nombreFile' => 'required|string|max:255',
            'tipoDocumentoFile' => 'required|integer',
            'tipoFile' => 'required|integer',
            'parentId' => 'nullable|integer',
            'createdByFile' => 'nullable|integer',
            'files' => 'nullable|array',
            'files.*' => 'file',
            'paths' => 'nullable|json'
        ]);

        $nombreFile = $request->input('nombreFile');
        $tipoDocumentoFile = $request->input('tipoDocumentoFile');
        $tipoFile = $request->input('tipoFile');
        $parentId = $request->input('parentId');
        $createdByFile = $request->input('createdByFile');
        $dateFolder = date('d-m-Y');

        if ($tipoFile == 1) {
            if ($request->hasFile('files')) {
                $storagePath = $tipoDocumentoFile == 1
                    ? "scanned_documents/file/{$dateFolder}/{$nombreFile}"
                    : "scanned_documents/mem-rap/{$dateFolder}/{$nombreFile}";

                if (!Storage::exists($storagePath)) {
                    Storage::makeDirectory($storagePath);
                }

                $carpeta = File::firstOrCreate([
                    'ruta_file' => $storagePath
                ], [
                    'nombre_file' => $nombreFile,
                    'tipo_documento_file' => $tipoDocumentoFile,
                    'tipo_file' => $tipoFile,
                    'created_by_file' => $createdByFile,
                    'parent_id' => $parentId,
                    'persona_id' => null,
                    'estado_file' => 1,
                ]);

                $paths = json_decode($request->input('paths'));

                foreach ($request->file('files') as $index => $uploadedFile) {
                    $uploadedFilePath = $paths[$index]->filePath;
                    $pathParts = explode('/', $uploadedFilePath);
                    $initialPath = $storagePath;
                    $lastParentId = $carpeta->id_file;

                    foreach ($pathParts as $key => $pathPart) {
                        if ($key != 0) {
                            if ($pathPart != $uploadedFile->getClientOriginalName()) {
                                $newPath = $initialPath . '/' . $pathPart;
                                Storage::makeDirectory($newPath);

                                $subcarpeta = File::firstOrCreate([
                                    'ruta_file' => $newPath
                                ], [
                                    'nombre_file' => $pathPart,
                                    'tipo_documento_file' => $tipoDocumentoFile,
                                    'tipo_file' => 1,
                                    'created_by_file' => $createdByFile,
                                    'parent_id' => $lastParentId,
                                    'persona_id' => null,
                                    'estado_file' => 1,
                                ]);

                                $lastParentId = $subcarpeta->id_file;
                                $initialPath = $newPath;
                            }
                        }
                    }

                    $fileStoragePath = storage_path("app/{$initialPath}/");

                    if (!file_exists($fileStoragePath)) {
                        mkdir($fileStoragePath, 0777, true);
                    }

                    $fileNameWithExtension = $uploadedFile->getClientOriginalName();
                    if (!$uploadedFile->move($fileStoragePath, $fileNameWithExtension)) {
                        return response()->json(['message' => 'Error al mover el archivo.'], 500);
                    }

                    $filePath = "{$initialPath}/{$fileNameWithExtension}";

                    $fileNameWithExtension = $uploadedFile->getClientOriginalName();
                    $uniqueFileName = $fileNameWithExtension;
                    $fileName = pathinfo($fileNameWithExtension, PATHINFO_FILENAME);

                    $persona = is_numeric($fileName)
                        ? Persona::where('ci_persona', $fileName)->first()
                        : Persona::whereRaw('CONCAT(nombre_persona, " ", primer_apellido_persona, " ", segundo_apellido_persona) LIKE ?', ['%' . $fileName . '%'])->first();

                    File::create([
                        'persona_id' => $persona ? $persona->id_persona : null,
                        'nombre_file' => $fileNameWithExtension,
                        'tipo_file' => 2,
                        'tipo_documento_file' => $tipoDocumentoFile,
                        'ruta_file' => $filePath,
                        'parent_id' => $lastParentId,
                        'created_by_file' => $createdByFile,
                        'estado_file' => 1,
                    ]);
                }
                return response()->json(['message' => 'Carpeta y documento creados exitosamente.'], 201);
            } else {
                $storagePath = $tipoDocumentoFile == 1
                    ? "scanned_documents/file/{$dateFolder}/{$nombreFile}"
                    : "scanned_documents/mem-rap/{$dateFolder}/{$nombreFile}";

                Storage::makeDirectory($storagePath);

                $carpeta = File::create([
                    'nombre_file' => $nombreFile,
                    'tipo_documento_file' => $tipoDocumentoFile,
                    'ruta_file' => $storagePath,
                    'tipo_file' => $tipoFile,
                    'created_by_file' => $createdByFile,
                    'parent_id' => $parentId,
                    'estado_file' => 1,
                ]);

                if ($carpeta) {
                    return response()->json(['message' => 'Carpeta creada exitosamente.'], 201);
                } else {
                    return response()->json(['message' => 'Error al crear la carpeta.'], 500);
                }
            }
        } else {
            try {
                $uploadedFile = $request->file('file');

                $storagePath = $tipoDocumentoFile == 1
                    ? "scanned_documents/file/{$dateFolder}/{$nombreFile}"
                    : "scanned_documents/mem-rap/{$dateFolder}/{$nombreFile}";


                if (!$uploadedFile) {
                    return response()->json(['message' => 'No se ha subido ningún archivo.'], 400);
                }

                if (!file_exists($storagePath)) {
                    mkdir($storagePath, 0777, true);
                }

                $fileNameWithExtension = $uploadedFile->getClientOriginalName();
                $uniqueFileName = $fileNameWithExtension;
                $fileName = pathinfo($fileNameWithExtension, PATHINFO_FILENAME);

                if (!$uploadedFile->move($storagePath, $uniqueFileName)) {
                    return response()->json(['message' => 'Error al mover el archivo.'], 500);
                }

                if ($tipoDocumentoFile == 1) {
                    $filePath = "scanned_documents/file/{$dateFolder}/{$uniqueFileName}";
                } else {
                    $filePath = "scanned_documents/mem-rap/{$dateFolder}/{$uniqueFileName}";
                }

                $persona = is_numeric($fileName)
                    ? Persona::where('ci_persona', $fileName)->first()
                    : Persona::whereRaw('CONCAT(nombre_persona, " ", primer_apellido_persona, " ", segundo_apellido_persona) LIKE ?', ['%' . $fileName . '%'])->first();

                $documento = File::create([
                    'persona_id' => $persona ? $persona->id_persona : null,
                    'nombre_file' => $uniqueFileName,
                    'tipo_file' => $tipoFile,
                    'tipo_documento_file' => $tipoDocumentoFile,
                    'ruta_file' => $filePath,
                    'parent_id' => $parentId,
                    'created_by_file' => $createdByFile,
                    'estado_file' => 1,
                ]);

                return response()->json(['message' => 'Documento guardado exitosamente.'], 201);
            } catch (Exception $e) {
                return response()->json(['message' => 'Error al guardar el documento: ' . $e->getMessage()], 500);
            }
        }
    }

    public function listarFile(Request $request)
    {
        $personaDocumento = $request->input('filtro');

        $query = File::select([
            'dde_files.id_file',
            'dde_files.nombre_file',
            'users.name as propietario',
            'dde_files.updated_at',
            'dde_files.ruta_file',
            'dde_files.tipo_documento_file',
            'dde_files.tipo_file',
            'dde_files.parent_id'
        ])
            ->leftJoin('dde_personas', 'dde_files.persona_id', '=', 'dde_personas.id_persona')
            ->leftJoin('users', 'dde_files.created_by_file', '=', 'users.id')
            ->where('dde_files.estado_file', 1)
            ->where('dde_files.tipo_documento_file', 1)
            ->orderByRaw("CASE WHEN dde_files.tipo_file = 1 THEN 1 WHEN dde_files.tipo_file = 2 THEN 2 END")
            ->orderBy('dde_files.id_file', 'desc');

        $query->where(function ($subQuery) use ($personaDocumento) {
            $subQuery->where('dde_files.nombre_file', 'LIKE', '%' . $personaDocumento . '%')
                ->orWhere(function ($q) use ($personaDocumento) {
                    $q->where('dde_personas.ci_persona', 'LIKE', '%' . $personaDocumento . '%')
                        ->orWhereRaw(
                            "CONCAT(dde_personas.nombre_persona, ' ', COALESCE(dde_personas.primer_apellido_persona, ''), ' ', COALESCE(dde_personas.segundo_apellido_persona, '')) LIKE ?",
                            ['%' . $personaDocumento . '%']
                        );
                });
        });

        $results = $query->get();

        return response()->json($results);
    }

    public function listarMemoRap(Request $request)
    {
        $personaDocumento = $request->input('filtro');

        $query = File::select([
            'dde_files.id_file',
            'dde_files.nombre_file',
            'users.name as propietario',
            'dde_files.updated_at',
            'dde_files.ruta_file',
            'dde_files.tipo_documento_file',
            'dde_files.tipo_file',
            'dde_files.parent_id'
        ])
            ->leftJoin('dde_personas', 'dde_files.persona_id', '=', 'dde_personas.id_persona')
            ->leftJoin('users', 'dde_files.created_by_file', '=', 'users.id')
            ->where('dde_files.estado_file', 1)
            ->where('dde_files.tipo_documento_file', 2)
            ->orderByRaw("CASE WHEN dde_files.tipo_file = 1 THEN 1 WHEN dde_files.tipo_file = 2 THEN 2 END")
            ->orderBy('dde_files.id_file', 'desc');

        $query->where(function ($subQuery) use ($personaDocumento) {
            $subQuery->where('dde_files.nombre_file', 'LIKE', '%' . $personaDocumento . '%')
                ->orWhere(function ($q) use ($personaDocumento) {
                    $q->where('dde_personas.ci_persona', 'LIKE', '%' . $personaDocumento . '%')
                        ->orWhereRaw(
                            "CONCAT(dde_personas.nombre_persona, ' ', COALESCE(dde_personas.primer_apellido_persona, ''), ' ', COALESCE(dde_personas.segundo_apellido_persona, '')) LIKE ?",
                            ['%' . $personaDocumento . '%']
                        );
                });
        });

        $results = $query->get();

        return response()->json($results);
    }

    public function listarHijos(Request $request, $parentId)
    {
        $personaDocumento = $request->query('personaFile');

        $query = File::with(['persona', 'children', 'createdBy', 'modifiedBy'])
            ->where('parent_id', $parentId)
            ->where('estado_file', 1);

        if ($personaDocumento) {
            $query->where(function ($subQuery) use ($personaDocumento) {
                $subQuery->where('nombre_file', 'LIKE', '%' . $personaDocumento . '%')
                    ->orWhereHas('persona', function ($q) use ($personaDocumento) {
                        $q->where('ci_persona', 'LIKE', '%' . $personaDocumento . '%')
                            ->orWhereRaw(
                                "CONCAT(nombre_persona, ' ', COALESCE(primer_apellido_persona, ''), ' ', COALESCE(segundo_apellido_persona, '')) LIKE ?",
                                ['%' . $personaDocumento . '%']
                            );
                    });
            });
        }

        $hijos = $query->get();

        return response()->json($hijos);
    }

    public function verDocumento($fileId)
    {
        try {
            $documento = File::findOrFail($fileId);
            $filePath = storage_path("app/{$documento->ruta_file}");

            if (!file_exists($filePath)) {
                return response()->json(['error' => 'Archivo no encontrado.'], Response::HTTP_NOT_FOUND);
            }

            $fileName = $documento->nombre_file ?: 'documento.pdf';

            $response = response()->file($filePath, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename=\"{$fileName}\""
            ]);

            $response->headers->set('X-Document-Name', $fileName);

            return $response;
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error interno del servidor.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function downloadDocumento($fileId)
    {
        $document = File::findOrFail($fileId);
        $pathToFile = storage_path('app/' . $document->ruta_file);

        if (file_exists($pathToFile)) {
            return response()->download($pathToFile);
        } else {
            return response()->json(['message' => 'Archivo no encontrado'], 404);
        }
    }

    public function modificarFila(Request $request, $fileId)
    {
        $file = file::find($fileId);

        if (!$file) {
            return response()->json(['error' => 'File no encontrado'], 404);
        }

        $file->estado_file = 2;
        $file->modified_by_file = $request->input('modifiedByFile');
        $file->save();

        return response()->json(['message' => 'File dado de baja correctamente']);
    }

    public function darBajaFile(Request $request, $fileId)
    {
        $file = file::find($fileId);

        if (!$file) {
            return response()->json(['error' => 'File no encontrado'], 404);
        }

        $file->estado_file = 2;
        $file->modified_by_file = $request->input('modifiedByFile');
        $file->save();

        return response()->json(['message' => 'File dado de baja correctamente']);
    }

    public function mostrarNombreFile($id)
    {
        $file = File::findOrFail($id);
        return response()->json(['id_file' => $file->id_file, 'nombre_file' => $file->nombre_file]);
    }

    public function modificarNombreFile(Request $request)
    {
        $validatedData = $request->validate([
            'idFile' => 'nullable|integer',
            'nombreFile' => 'nullable|string',
            'modifiedByFile' => 'nullable|integer',
        ]);

        $id = $validatedData['idFile'];

        $updated = File::where('id_file', $id)->update([
            'nombre_file' => $validatedData['nombreFile'],
            'modified_by_file' => $validatedData['modifiedByFile'],
        ]);
        if ($updated) {
            return response()->json(['message' => 'File actualizado correctamente']);
        } else {
            return response()->json(['message' => 'No se encontró el file para actualizar'], 404);
        }
    }

    public function downloadCarpeta($fileId)
    {
        $file = File::where('id_file', $fileId)->where('tipo_file', 1)->first();

        if (!$file) {
            return response()->json(['error' => 'Carpeta no encontrado'], 404);
        }

        $path = $file->ruta_file;

        if (!Storage::exists($path)) {
            return response()->json(['error' => 'Carpeta no encontrada'], 404);
        }

        $files = Storage::allFiles($path);
        $directories = Storage::allDirectories($path);

        if (empty($files) && empty($directories)) {
            return response()->json(['error' => 'La carpeta está vacía'], 404);
        }

        $zip = new ZipArchive();
        $zipFileName = "$fileId.zip";
        $zipFilePath = storage_path("app/$zipFileName");

        if ($zip->open($zipFilePath, ZipArchive::CREATE) !== TRUE) {
            return response()->json(['error' => 'No se pudo crear el archivo zip'], 500);
        }

        foreach ($files as $file) {
            $relativePath = str_replace($path . '/', '', $file);
            $zip->addFile(storage_path("app/$file"), $relativePath);
        }

        $zip->close();

        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Persona;
use App\Models\Documento;


class DocumentoController extends Controller
{
    public function uploadScannedFolder(Request $request)
    {
        $tipoDocumento = $request->input('tipoDocumento');
        $files = $request->file('files');
        $createdByDocumento = $request->input('createdByDocumento');
        $dateFolder = date('d-m-Y');

        if (is_null($files)) {
            return response()->json(['error' => 'No se subieron archivos.'], 400);
        }

        if (!is_array($files)) {
            return response()->json(['error' => 'Los archivos deben ser un array'], 400);
        }

        foreach ($files as $file) {
            $fileNameWithExtension = $file->getClientOriginalName();
            $fileName = pathinfo($fileNameWithExtension, PATHINFO_FILENAME);

            if (is_numeric($fileName)) {
                $persona = Persona::where('ci_persona', $fileName)->first();
            } else {
                $persona = Persona::whereRaw('CONCAT(nombre_persona, " ", primer_apellido_persona, " ", segundo_apellido_persona) LIKE ?', ['%' . $fileName . '%'])->first();
            }

            $storagePath = storage_path("app/scan_documents/{$dateFolder}");
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0777, true);
            }

            $filePath = $file->storeAs($dateFolder, $fileNameWithExtension, 'local');

            if ($persona) {
                Documento::create([
                    'nombre_documento' => $fileName,
                    'ruta_archivo_documento' => $filePath,
                    'tipo_documento' => $tipoDocumento,
                    'persona_id' => $persona->id_persona,
                    'created_by_documento' => $createdByDocumento,
                ]);
            } else {
                Documento::create([
                    'nombre_documento' => $fileName,
                    'ruta_archivo_documento' => $filePath,
                    'tipo_documento' => $tipoDocumento,
                    'persona_id' => null,
                    'created_by_documento' => $createdByDocumento,
                ]);
            }
        }
        return response()->json(['message' => 'Archivos procesados correctamente']);
    }

    public function listarDocumentos(Request $request)
    {
        $limit = $request->input('limit');
        $page = $request->input('page');
        $personaDocumento = $request->input('query.personaDocumento');

        $query = Documento::select([
            'dde_documentos.id_documento',
            'dde_documentos.nombre_documento',
            'dde_documentos.ruta_archivo_documento',
            'dde_documentos.tipo_documento',
            'users.name'
        ])
            ->leftJoin('dde_personas', 'dde_documentos.persona_id', '=', 'dde_personas.id_persona') 
            ->leftJoin('users', 'dde_documentos.created_by_documento', '=', 'users.id')
            ->orderBy('dde_documentos.id_documento', 'desc');

        $query->where(function ($subQuery) use ($personaDocumento) {
            $subQuery->where('dde_documentos.nombre_documento', 'LIKE', '%' . $personaDocumento . '%')
                ->orWhere(function ($q) use ($personaDocumento) {
                    $q->where('dde_personas.ci_persona', 'LIKE', '%' . $personaDocumento . '%')
                        ->orWhereRaw(
                            "CONCAT(dde_personas.nombre_persona, ' ', COALESCE(dde_personas.primer_apellido_persona, ''), ' ', COALESCE(dde_personas.segundo_apellido_persona, '')) LIKE ?",
                            ['%' . $personaDocumento . '%']
                        );
                });
        });

        $users = $query->paginate($limit, ['*'], 'page', $page);

        return $this->sendPaginated($users);
    }
}

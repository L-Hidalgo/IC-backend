<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Persona;
use App\Models\Documento;
use Symfony\Component\HttpFoundation\Response;

class DocumentoController extends Controller
{
  /*  

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
            ->where('dde_documentos.estado_documento', 1)
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

    public function verDocumento($documentoId)
    {
        try {
            $documento = Documento::findOrFail($documentoId);
            $filePath = storage_path("app/scan_documents/{$documento->ruta_archivo_documento}");

            if (!file_exists($filePath)) {
                return response()->json(['error' => 'Archivo no encontrado.'], Response::HTTP_NOT_FOUND);
            }

            $fileName = $documento->nombre_documento ?: 'documento.pdf';

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

    public function downloadDocumento($documentoId)
    {
        $document = Documento::findOrFail($documentoId);
        $pathToFile = storage_path('app/scan_documents/' . $document->ruta_archivo_documento);

        if (file_exists($pathToFile)) {
            return response()->download($pathToFile);
        } else {
            return response()->json(['message' => 'Archivo no encontrado'], 404);
        }
    }

    public function darBajaDocumento(Request $request, $documentoId)
    {
        $documento = Documento::find($documentoId);

        if (!$documento) {
            return response()->json(['error' => 'Documento no encontrado'], 404);
        }

        $documento->estado_documento = 2;
        $documento->modified_by_documento = $request->input('modifiedByDocumento');
        $documento->save();

        return response()->json(['message' => 'Documento dado de baja correctamente']);
    }*/
}

<?php

namespace App\Http\Controllers;

use App\Models\Plantilla;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PlantillaController extends Controller
{
    public function downloadPlantillasIncorporacion($filename)
    {
        $allowedFiles = [
            'R-0078' => 'R-0078.docx',
            'R-0980' => 'R-0980.docx',
            'R-1401' => 'R-1401.docx',
            'R-1023' => 'R-1023.docx',
            'R-1129' => 'R-1129.docx',

            'infMinutaIncorporacion' => 'infMinutaIncorporacion.docx',
            'infNotaIncorporacion' => 'infNotaIncorporacion.docx',
            'memorandumIncorporacion' => 'memorandumIncorporacion.docx',
            'rapIncorporacion' => 'rapIncorporacion.docx',

            'infMinutaIncorporacionLibreNombramiento' => 'infMinutaIncorporacionLibreNombramiento.docx',
            'infNotaIncorporacionLibreNombramiento' => 'infNotaIncorporacionLibreNombramiento.docx',
            'memorandumIncorporacionLibreNombramiento' => 'memorandumIncorporacionLibreNombramiento.docx',
            'rapIncorporacionLibreNombramiento' => 'rapIncorporacionLibreNombramiento.docx',

            'infMinutaCambioItem' => 'infMinutaCambioItem.docx',
            'infNotaCambioItem' => 'infNotaCambioItem.docx',
            'memorandumCambioItem' => 'memorandumCambioItem.docx',
            'rapCambioItem' => 'rapCambioItem.docx',

            'infMinutaCambioItemLibreNombramiento' => 'infMinutaCambioItemLibreNombramiento.docx',
            'infNotaCambioItemLibreNombramiento' => 'infNotaCambioItemLibreNombramiento.docx',
            'memorandumCambioItemLibreNombramiento' => 'memorandumCambioItemLibreNombramiento.docx',
            'rapCambioItemLibreNombramiento' => 'rapCambioItemLibreNombramiento.docx',

            'R-1418' => 'R-1418.xlsx',
            'R-1419' => 'R-1419.xlsx',
            'actaEntrega' => 'actaEntrega.docx',
            'actaPosesion' => 'actaPosesion.docx',
            'R-0716' => 'R-0716.docx',
            'R-0976' => 'R-0976.docx',
            'R-0921' => 'R-0921.docx',
            'R-1469' => 'R-1469.docx',
            'R-SGC-0033' => 'R-SGC-0033.docx'
        ];

        if (!array_key_exists($filename, $allowedFiles)) {
            return response()->json(['error' => 'File not found'], Response::HTTP_NOT_FOUND);
        }

        $path = storage_path('app/form_templates/incorporaciones/' . $allowedFiles[$filename]);

        if (!file_exists($path)) {
            return response()->json(['error' => 'File not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->download($path);
    }

    public function downloadPlantillasInterinato($filename)
    {
        $allowedFiles = [
            'informeCombinadoGerente' => 'informeCombinadoGerente.docx',
            'informeGerente' => 'informeGerente.docx',
            'memorandumGerente' => 'memorandumGerente.docx',
            'rapGerente' => 'rapGerente.docx',
            'informeCombinadoJefe' => 'informeCombinadoJefe.docx',
            'informeJefe' => 'informeJefe.docx',
            'memorandumJefe' => 'memorandumJefe.docx',
            'rapJefe' => 'rapJefe.docx',
            'memorandumSuspencion' => 'memorandumSuspencion.docx',
        ];

        if (!array_key_exists($filename, $allowedFiles)) {
            return response()->json(['error' => 'File not found'], Response::HTTP_NOT_FOUND);
        }

        $path = storage_path('app/form_templates/interinatos/' . $allowedFiles[$filename]);

        if (!file_exists($path)) {
            return response()->json(['error' => 'File not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->download($path);
    }

    public function uploadPlantilla(Request $request)
    {
        $filename = $request->input('nombrePlantilla');
        $file = $request->file('file');
        $tipoPlantilla = $request->input('tipoPlantilla');
        $createdPlantilla = $request->input('createdPlantilla');

        $allowedFiles = $tipoPlantilla == 1 ? [
            'R-0078.docx',
            'R-0980.docx',
            'R-1401.docx',
            'R-1023.docx',
            'R-1129.docx',
            'infMinutaIncorporacion.docx',
            'infNotaIncorporacion.docx',
            'memorandumIncorporacion.docx',
            'rapIncorporacion.docx',
            'infMinutaIncorporacionLibreNombramiento.docx',
            'infNotaIncorporacionLibreNombramiento.docx',
            'memorandumIncorporacionLibreNombramiento.docx',
            'rapIncorporacionLibreNombramiento.docx',
            'infMinutaCambioItem.docx',
            'infNotaCambioItem.docx',
            'memorandumCambioItem.docx',
            'rapCambioItem.docx',
            'infMinutaCambioItemLibreNombramiento.docx',
            'infNotaCambioItemLibreNombramiento.docx',
            'memorandumCambioItemLibreNombramiento.docx',
            'rapCambioItemLibreNombramiento.docx',
            'R-1418.xlsx',
            'R-1419.xlsx',
            'actaEntrega.docx',
            'actaPosesion.docx',
            'R-0716.docx',
            'R-0976.docx',
            'R-0921.docx',
            'R-1469.docx',
            'R-SGC-0033.docx'
        ] : [
            'informeCombinadoGerente.docx',
            'informeGerente.docx',
            'memorandumGerente.docx',
            'rapGerente.docx',
            'informeCombinadoJefe.docx',
            'informeJefe.docx',
            'memorandumJefe.docx',
            'rapJefe.docx',
            'memorandumSuspencion.docx',
        ];

        if (in_array($filename, $allowedFiles)) {
            $date = now()->format('Y-m-d');
            $backupDirectory = storage_path('app/plantillas_backup/' . ($tipoPlantilla == 1 ? 'incorporaciones' : 'interinatos') . '/' . $date);

            if (!file_exists($backupDirectory)) {
                mkdir($backupDirectory, 0755, true);
            }

            $originalPath = storage_path('app/form_templates/' . ($tipoPlantilla == 1 ? 'incorporaciones' : 'interinatos') . '/' . $filename);
            $backupPath = $backupDirectory . '/' . $filename;

            $currentVersion = Plantilla::where('nombre_plantilla', $filename)->count();
            $newVersion = $currentVersion + 1;

            if (file_exists($originalPath)) {
                rename($originalPath, $backupPath);

                Plantilla::create([
                    'nombre_plantilla' => $filename,
                    'version_plantilla' => $newVersion,
                    'tipo_plantilla' => $tipoPlantilla,
                    'ruta_plantilla' => $backupPath,
                    'created_plantilla' => $createdPlantilla,
                ]);
            }

            $file->move(storage_path('app/form_templates/' . ($tipoPlantilla == 1 ? 'incorporaciones' : 'interinatos')), $filename);

            return response()->json(['message' => 'Archivo subido y reemplazado exitosamente.']);
        } else {
            return response()->json(['error' => "El archivo '$filename' no coincide con los archivos permitidos."], 400);
        }
    }

    public function uploadPlantillaInterinatos(Request $request)
    {
        $filename = $request->input('nombrePlantilla');
        $file = $request->file('file');
        $tipoPlantilla = $request->input('tipoPlantilla');
        $createdPlantilla = $request->input('createdPlantilla');

        $allowedFiles = [
            'informeCombinadoGerente.docx',
            'informeGerente.docx',
            'memorandumGerente.docx',
            'rapGerente.docx',
            'informeCombinadoJefe.docx',
            'informeJefe.docx',
            'memorandumJefe.docx',
            'rapJefe.docx',
            'memorandumSuspencion.docx',
        ];

        if (in_array($filename, $allowedFiles)) {
            $date = now()->format('Y-m-d');
            $backupDirectory = storage_path('app/plantillas_backup/interinatos' . $date);

            if (!file_exists($backupDirectory)) {
                mkdir($backupDirectory, 0755, true);
            }

            $originalPath = storage_path('app/form_templates/interinatos' . $filename);
            $backupPath = $backupDirectory . '/' . $filename;

            $currentVersion = Plantilla::where('nombre_plantilla', $filename)->count();
            $newVersion = $currentVersion + 1;

            if (file_exists($originalPath)) {
                rename($originalPath, $backupPath);

                Plantilla::create([
                    'nombre_plantilla' => $filename,
                    'version_plantilla' => $newVersion,
                    'tipo_plantilla' => $tipoPlantilla,
                    'ruta_plantilla' => $backupPath,
                    'created_plantilla' => $createdPlantilla,
                ]);
            }

            $file->move(storage_path('app/form_templates/interinatos'), $filename);

            return response()->json(['message' => 'Archivo subido y reemplazado exitosamente.']);
        } else {
            return response()->json(['error' => "El archivo '$filename' no coincide con los archivos permitidos."], 400);
        }
    }

    public function listarPlantillas(Request $request)
    {
        $limit = $request->input('limit');
        $page = $request->input('page');

        $nombrePlantilla = $request->input('query.nombrePlantilla');
        $versionPlantilla = $request->input('query.versionPlantilla');
        $fechaInicioPlantilla = $request->input('query.fechaInicioPlantilla');
        $fechaFinPlantilla = $request->input('query.fechaFinPlantilla');

        $query = Plantilla::with(['createdBy', 'modifiedBy'])
            ->orderBy('id_plantilla', 'desc');

        if ($nombrePlantilla) {
            $query->where('nombre_plantilla', 'LIKE', '%' . $nombrePlantilla . '%');
        }

        if ($versionPlantilla) {
            $query->where('version_plantilla', 'LIKE', '%' . $versionPlantilla . '%');
        }

        if ($fechaInicioPlantilla) {
            $query->whereDate('created_at', '>=', $fechaInicioPlantilla);
        }

        if ($fechaFinPlantilla) {
            $query->whereDate('created_at', '<=', $fechaFinPlantilla);
        }

        $plantillas = $query->paginate($limit, ['*'], 'page', $page);

        return $this->sendPaginated($plantillas);
    }

    public function revertirPlantilla($plantillaId, Request $request)
    {
        $storagePath = storage_path('app/form_templates');

        $plantilla = Plantilla::find($plantillaId);

        if (!$plantilla) {
            return response()->json(['error' => 'Plantilla no encontrada'], 404);
        }

        $nombrePlantilla = $plantilla->nombre_plantilla;
        $rutaPlantilla = $plantilla->ruta_plantilla;

        $allowedFiles = [
            'R-0078.docx',
            'R-0980.docx',
            'R-1401.docx',
            'R-1023.docx',
            'R-1129.docx',
            'infMinutaIncorporacion.docx',
            'infNotaIncorporacion.docx',
            'memorandumIncorporacion.docx',
            'rapIncorporacion.docx',
            'infMinutaIncorporacionLibreNombramiento.docx',
            'infNotaIncorporacionLibreNombramiento.docx',
            'memorandumIncorporacionLibreNombramiento.docx',
            'rapIncorporacionLibreNombramiento.docx',
            'infMinutaCambioItem.docx',
            'infNotaCambioItem.docx',
            'memorandumCambioItem.docx',
            'rapCambioItem.docx',
            'infMinutaCambioItemLibreNombramiento.docx',
            'infNotaCambioItemLibreNombramiento.docx',
            'memorandumCambioItemLibreNombramiento.docx',
            'rapCambioItemLibreNombramiento.docx',
            'R-1418.xlsx',
            'R-1419.xlsx',
            'actaEntrega.docx',
            'actaPosesion.docx',
            'R-0716.docx',
            'R-0976.docx',
            'R-0921.docx',
            'R-1469.docx',
            'R-SGC-0033.docx'
        ];

        $fileToDelete = $storagePath . '/' . $nombrePlantilla;
        if (in_array($nombrePlantilla, $allowedFiles) && file_exists($fileToDelete)) {
            unlink($fileToDelete);
        }

        if (file_exists($rutaPlantilla)) {
            copy($rutaPlantilla, $fileToDelete);
        } else {
            return response()->json(['error' => 'Archivo original no encontrado'], 404);
        }

        return response()->json(['success' => 'Plantilla revertida correctamente']);
    }
}

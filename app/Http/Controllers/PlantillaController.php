<?php

namespace App\Http\Controllers;

use App\Models\Plantilla;
use Illuminate\Http\Request;



class PlantillaController extends Controller
{
    public function uploadPlantilla(Request $request)
    {
        $filename = $request->input('nombrePlantilla');
        $file = $request->file('file');
        $tipoPlantilla = $request->input('tipoPlantilla');
        $createdPlantilla = $request->input('createdPlantilla');

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

        if (in_array($filename, $allowedFiles)) {
            $date = now()->format('Y-m-d');
            $backupDirectory = storage_path('app/plantillas_backup/' . $date);

            if (!file_exists($backupDirectory)) {
                mkdir($backupDirectory, 0755, true);
            }

            $originalPath = storage_path('app/form_templates/' . $filename);
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

            $file->move(storage_path('app/form_templates'), $filename);

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

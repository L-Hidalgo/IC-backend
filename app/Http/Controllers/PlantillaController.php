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

            'incorporacion/infMinutaIncorporacion.docx',
            'incorporacion/infNotaIncorporacion.docx',
            'incorporacion/memorandumIncorporacion.docx',
            'incorporacion/rapIncorporacion.docx',

            'libreNombramiento/incorporacion/infMinutaIncorporacionLibreNombramiento.docx',
            'libreNombramiento/incorporacion/infNotaIncorporacionLibreNombramiento.docx',
            'libreNombramiento/incorporacion/memorandumIncorporacionLibreNombramiento.docx',
            'libreNombramiento/incorporacion/rapIncorporacionLibreNombramiento.docx',

            'cambioItem/infMinutaCambioItem.docx',
            'cambioItem/infNotaCambioItem.docx',
            'cambioItem/memorandumCambioItem.docx',
            'cambioItem/rapCambioItem.docx',

            'libreNombramiento/cambioItem/infMinutaCambioItemLibreNombramiento.docx',
            'libreNombramiento/cambioItem/infNotaCambioItemLibreNombramiento.docx',
            'libreNombramiento/cambioItem/memorandumCambioItemLibreNombramiento.docx',
            'libreNombramiento/cambioItem/rapCambioItemLibreNombramiento.docx',

            'R-1418.xlsx',
            'R-1419.xlsx',
            'actaEntrega.docx',
            'actaPosesion.docx',
            'incorporacion/R-0716.docx',
            'incorporacion/R-0976.docx',
            'incorporacion/R-0921.docx',
            'R-1469.docx',
            'incorporacion/R-SGC-0033.docx'
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
            return response()->json(['error' => 'Archivo no permitido.'], 400);
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
        $plantilla = Plantilla::find($plantillaId);

        if (!$plantilla) {
            return response()->json(['message' => 'Plantilla no encontrada'], 404);
        }

        $nombrePlantilla = $plantilla->nombre_plantilla;
        $rutaPlantillaActual = $plantilla->ruta_plantilla;

        $allowedFiles = [
            'R-0078.docx',
            'R-0980.docx',
            'R-1401.docx',
            'R-1023.docx',
            'R-1129.docx',
            'incorporacion/infMinutaIncorporacion.docx',
            'incorporacion/infNotaIncorporacion.docx',
            'incorporacion/memorandumIncorporacion.docx',
            'incorporacion/rapIncorporacion.docx',
            'libreNombramiento/incorporacion/infMinutaIncorporacionLibreNombramiento.docx',
            'libreNombramiento/incorporacion/infNotaIncorporacionLibreNombramiento.docx',
            'libreNombramiento/incorporacion/memorandumIncorporacionLibreNombramiento.docx',
            'libreNombramiento/incorporacion/rapIncorporacionLibreNombramiento.docx',
            'cambioItem/infMinutaCambioItem.docx',
            'cambioItem/infNotaCambioItem.docx',
            'cambioItem/memorandumCambioItem.docx',
            'cambioItem/rapCambioItem.docx',
            'libreNombramiento/cambioItem/infMinutaCambioItemLibreNombramiento.docx',
            'libreNombramiento/cambioItem/infNotaCambioItemLibreNombramiento.docx',
            'libreNombramiento/cambioItem/memorandumCambioItemLibreNombramiento.docx',
            'libreNombramiento/cambioItem/rapCambioItemLibreNombramiento.docx',
            'R-1418.xlsx',
            'R-1419.xlsx',
            'actaEntrega.docx',
            'actaPosesion.docx',
            'incorporacion/R-0716.docx',
            'incorporacion/R-0976.docx',
            'incorporacion/R-0921.docx',
            'R-1469.docx',
            'incorporacion/R-SGC-0033.docx'
        ];

     
    }


    private function getLocalFiles()
    {
        return [
            'R-0078.docx',
            'R-0980.docx',
            'R-1401.docx',
            'R-1023.docx',
            'R-1129.docx',
            'incorporacion/infMinutaIncorporacion.docx',
            'incorporacion/infNotaIncorporacion.docx',
            'incorporacion/memorandumIncorporacion.docx',
            'incorporacion/rapIncorporacion.docx',
            'libreNombramiento/incorporacion/infMinutaIncorporacionLibreNombramiento.docx',
            'libreNombramiento/incorporacion/infNotaIncorporacionLibreNombramiento.docx',
            'libreNombramiento/incorporacion/memorandumIncorporacionLibreNombramiento.docx',
            'libreNombramiento/incorporacion/rapIncorporacionLibreNombramiento.docx',
            'cambioItem/infMinutaCambioItem.docx',
            'cambioItem/infNotaCambioItem.docx',
            'cambioItem/memorandumCambioItem.docx',
            'cambioItem/rapCambioItem.docx',
            'libreNombramiento/cambioItem/infMinutaCambioItemLibreNombramiento.docx',
            'libreNombramiento/cambioItem/infNotaCambioItemLibreNombramiento.docx',
            'libreNombramiento/cambioItem/memorandumCambioItemLibreNombramiento.docx',
            'libreNombramiento/cambioItem/rapCambioItemLibreNombramiento.docx',
            'R-1418.xlsx',
            'R-1419.xlsx',
            'actaEntrega.docx',
            'actaPosesion.docx',
            'incorporacion/R-0716.docx',
            'incorporacion/R-0976.docx',
            'incorporacion/R-0921.docx',
            'R-1469.docx',
            'incorporacion/R-SGC-0033.docx'
        ];
    }
}

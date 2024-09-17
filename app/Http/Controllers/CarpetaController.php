<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Carpeta;

class CarpetaController extends Controller
{
    public function crearCarpetaFile(Request $request)
    {
        $validated = $request->validate([
            'nombreCarpeta' => 'required|string|max:255',
            'createdByCarpeta' => 'required|integer',
            'padreIdCarpeta' => 'nullable|integer'
        ]);
        $dateFolder = date('d-m-Y');
        $folderPath = "scan_documents/file/{$dateFolder}/{$validated['nombreCarpeta']}";

        if (Storage::exists($folderPath)) {
            return response()->json(['message' => 'La carpeta ya existe.'], 400);
        }

        Storage::makeDirectory($folderPath);

        $folder = Carpeta::create([
            'nombre_carpeta' => $validated['nombreCarpeta'],
            'ruta_carpeta' => $folderPath,
            'tipo_carpeta' => 1, 
            'padre_id_carpeta' => $validated['padreIdCarpeta'] ?? null,
            'created_by_carpeta' => $validated['createdByCarpeta'],
            'estado_carpeta' => 1
        ]);

        return response()->json(['message' => 'Carpeta creada exitosamente.', 'CARPETA' => $folder]);
    }

    public function listar()
    {
        $areasFormaci_personaon = AreaFormacion::select(['id_area_formacion', 'nombre_area_formacion'])->get();
        return $this->sendList($areasFormaci_personaon);
    }

    public function listarCarpetas(Request $request)
    {
        $limit = $request->input('limit', 15);
        $page = $request->input('page', 1);
        $nombreCarpeta = $request->input('query.nombreCarpeta', '');

        $query = Carpeta::select([
            'dde_carpetas.id_carpeta',
            'dde_carpetas.nombre_carpeta',
            'dde_carpetas.ruta_carpeta',
            'dde_carpetas.estado_carpeta',
            'dde_carpetas.created_by_carpeta',
            'dde_carpetas.updated_at',
        ])
            ->with('createdBy')
            ->where('dde_carpetas.estado_carpeta', 1)
            ->orderBy('dde_carpetas.id_carpeta', 'desc');

        if (!empty($nombreCarpeta)) {
            $query->where('dde_carpetas.nombre_carpeta', 'LIKE', '%' . $nombreCarpeta . '%');
        }

        $carpetas = $query->paginate($limit, ['*'], 'page', $page);

        return $this->sendPaginated($carpetas);
    }
}

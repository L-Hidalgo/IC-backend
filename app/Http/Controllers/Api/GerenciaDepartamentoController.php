<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gerencia;

class GerenciaDepartamentoController extends Controller
{
    public function GerenciaDepartamento()
    {
        $gerencias = Gerencia::with('departamentos:id_departamento,gerencia_id,nombre_departamento')
                             ->orderBy('id_gerencia')
                             ->get(['id_gerencia', 'nombre_gerencia']);

        $formattedData = [];

        foreach ($gerencias as $gerencia) {
            $departamentos = $gerencia->departamentos->map(function ($departamento) {
                return [
                    'idDepartamento' => $departamento->id_departamento,
                    'nombreDepartamento' => $departamento->nombre_departamento,
                ];
            });

            $formattedData[] = [
                'idGerencia' => $gerencia->id_gerencia,
                'nombreGerencia' => $gerencia->nombre_gerencia,
                'departamentos' => $departamentos,
            ];
        }

        return response()->json($formattedData);
    }
}

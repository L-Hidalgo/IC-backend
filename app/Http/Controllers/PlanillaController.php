<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Puesto;
use Illuminate\Support\Facades\DB;

class PlanillaController extends Controller
{
    public function listarPuestosPersonas(Request $request)
    {
        $limit = 9;
        $page = $request->input('page', 1);

        // Filtros
        $item = $request->input('item');
        $gerenciasIds = $request->input('gerenciasIds');
        $departamentosIds = $request->input('departamentosIds');
        $estado = $request->input('estado');

        $query = Puesto::query()
            ->with(['departamento.gerencia', 'requisitos', 'persona_actual', 'funcionario.persona'])
            ->select([
                'dde_puestos.id_puesto as id',
                'dde_puestos.item_puesto as item',
                'dde_puestos.denominacion_puesto as denominacion',
                'dde_puestos.estado_id as estado',
                'dde_puestos.salario_puesto as salario',
                'departamentos.nombre_departamento as departamento',
                'dde_puestos.objetivo_puesto as objetivo',
                //'requisitos.formacion_requerida as formacion_requerida',
                'requisitos.experiencia_profesional_segun_cargo as experiencia_profesional_segun_cargo',
                'requisitos.experiencia_relacionado_al_area as experiencia_relacionado_al_area',
                'requisitos.experiencia_en_funciones_de_mando as experiencia_en_funciones_de_mando',
                'dde_puestos.persona_actual_id'
            ])
            ->leftJoin('dde_departamentos as departamentos', 'departamentos.id_departamento', '=', 'dde_puestos.departamento_id')
            ->leftJoin('dde_requisitos as requisitos', 'requisitos.puesto_id', '=', 'dde_puestos.id_puesto');

        if (isset($item)) {
            $query->where('dde_puestos.item_puesto', $item);
        }
        if (isset($departamentosIds) && count($departamentosIds) > 0) {
            $query->whereIn('dde_puestos.departamento_id', $departamentosIds);
        }
        if (isset($gerenciasIds) && count($gerenciasIds) > 0) {
            $query->whereIn('departamentos.gerencia_id', $gerenciasIds);
        }
        if (isset($estado)) {
            $query->where('dde_puestos.estado_id', $estado);
        }

        $query->orderBy('dde_puestos.item_puesto');

        // Pagination
        $personaPuestos = $query->paginate($limit, ['*'], 'page', $page);

        return response()->json($personaPuestos);
    }

}

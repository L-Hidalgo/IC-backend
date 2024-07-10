<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use App\Models\Puesto;
use Illuminate\Http\Request;

class PlanillaController extends Controller
{

    public function listarPuestos(Request $request)
    {
        $limit = $request->input('limit');
        $page = $request->input('page', 0);

        $query = Puesto::query()
            ->leftJoin('dde_estados as estado', 'dde_puestos.estado_id', '=', 'estado.id_estado')
            ->leftJoin('dde_departamentos as departamento', 'dde_puestos.departamento_id', '=', 'departamento.id_departamento')
            ->leftJoin('dde_personas as persona', 'dde_puestos.persona_actual_id', '=', 'persona.id_persona')
            ->leftJoin('dde_gerencias as gerencia', 'departamento.gerencia_id', '=', 'gerencia.id_gerencia');

        $query = $query->select([
            'dde_puestos.id_puesto as idPuesto',
            'dde_puestos.item_puesto as item',
            'dde_puestos.denominacion_puesto as denominacionPuesto',
            'dde_puestos.estado_id as estadoId',
            'estado.nombre_estado as estado',
            'gerencia.nombre_gerencia as gerencia',
            'dde_puestos.departamento_id as departamentoId',
            'departamento.nombre_departamento as departamento',
            'dde_puestos.persona_actual_id as personaId',
            'persona.nombre_persona as nombrePersona',
            'persona.primer_apellido_persona as primerApellidoPersona',
            'persona.segundo_apellido_persona as segundoApellidoPersona'
        ]);

        $query = $query->orderBy('dde_puestos.id_puesto');

        $personaPuestos = $query->paginate($limit, ['*'], 'page', $page);

        return response()->json($personaPuestos);
    }

    public function getImagenFuncionario($personaId)
    {
        $persona = Persona::find($personaId);

        if ($persona) {
            $imagen = $persona->imagenes()->latest()->first();

            if ($imagen) {
                $base64_imagen = $imagen->base64_imagen;
                $tipo_mime_imagen = $imagen->tipo_mime_imagen;
                $imagen_data = base64_decode($base64_imagen);

                return response($imagen_data)
                    ->header('Content-Type', 'image/jpeg')
                    ->header('Content-Disposition', 'inline')
                    ->header('Content-Length', strlen($imagen_data));
            } else {
                $imagenPorDefecto = public_path('img/funcionario.png');
                $imagen_data = file_get_contents($imagenPorDefecto);
                $tipo_mime_imagen = mime_content_type($imagenPorDefecto);

                return response($imagen_data)
                    ->header('Content-Type', $tipo_mime_imagen)
                    ->header('Content-Disposition', 'inline')
                    ->header('Content-Length', filesize($imagenPorDefecto));
            }
        } else {
            return response()->json(['message' => 'No se encontró a la persona.'], 404);
        }
    }

    public function byFiltrosPlanilla(Request $request)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);

        $itemNombre = $request->input('query.itemNombre');
        $gerenciasIds = $request->input('query.gerenciasIds', []);
        $departamentosIds = $request->input('query.departamentosIds', []);
        $estadoId = $request->input('query.estadoPuesto');

        $query = Puesto::query()
            ->leftJoin('dde_estados as estado', 'dde_puestos.estado_id', '=', 'estado.id_estado')
            ->leftJoin('dde_departamentos as departamento', 'dde_puestos.departamento_id', '=', 'departamento.id_departamento')
            ->leftJoin('dde_personas as persona', 'dde_puestos.persona_actual_id', '=', 'persona.id_persona')
            ->leftJoin('dde_gerencias as gerencia', 'departamento.gerencia_id', '=', 'gerencia.id_gerencia');

        if (!empty($itemNombre)) {
            $query->where(function ($query) use ($itemNombre) {
                $query->where('dde_puestos.item_puesto', $itemNombre)
                    ->orWhere('persona.nombre_persona', 'LIKE', "%{$itemNombre}%")
                    ->orWhere('persona.primer_apellido_persona', 'LIKE', "%{$itemNombre}%")
                    ->orWhere('persona.segundo_apellido_persona', 'LIKE', "%{$itemNombre}%");
            });
        }

        if (!empty($gerenciasIds)) {
            $query->whereIn('departamento.gerencia_id', $gerenciasIds);
        }

        if (!empty($departamentosIds)) {
            $query->whereIn('departamento.id_departamento', $departamentosIds);
        }

        if (!empty($estadoId)) {
            $query->where('estado.id_estado', $estadoId);
        }

        $query->select([
            'dde_puestos.id_puesto as idPuesto',
            'dde_puestos.item_puesto as item',
            'dde_puestos.denominacion_puesto as denominacionPuesto',
            'dde_puestos.estado_id as estadoId',
            'estado.nombre_estado as estado',
            'gerencia.nombre_gerencia as gerencia',
            'dde_puestos.departamento_id as departamentoId',
            'departamento.nombre_departamento as departamento',
            'dde_puestos.persona_actual_id as personaId',
            'persona.nombre_persona as nombrePersona',
            'persona.primer_apellido_persona as primerApellidoPersona',
            'persona.segundo_apellido_persona as segundoApellidoPersona'
        ]);

        $query->orderBy('dde_puestos.id_puesto');

        $personaPuestos = $query->paginate($limit, ['*'], 'page', $page);

        return response()->json($personaPuestos);
    }

    public function infPersonaPuesto($puestoId)
    {
        $personaPuesto = Puesto::with(['persona_actual', 'departamento.gerencia', 'requisitos', 'funcionario', 'estado'])->find($puestoId);

        return response()->json($personaPuesto);
    }
}

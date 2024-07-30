<?php

namespace App\Http\Controllers;

use App\Models\Interinato;
use App\Models\Puesto;
use Illuminate\Http\Request;
use Carbon\Carbon;


class InterinatoController extends Controller
{


    public function crearInterinato(Request $request)
    {
        $validatedData = $request->validate([
            'idInterinato' => 'nullable|integer',
            'proveidoTramiteInterinato' => 'nullable|string',
            'citeNotaInformeMinutaInterinato' => 'nullable|string',
            'fchCiteNotaInfMinutaInterinato' => 'nullable|date',

            'puestoNuevoId' => 'nullable|integer',
            'titularPuestoNuevoId' => 'nullable|integer',
            'puestoActualId' => 'nullable|integer',
            'titularPuestoActualId' => 'nullable|integer',

            'citeInformeInterinato' => 'nullable|string',
            'fojasInformeInterinato' => 'nullable|string',
            'citeMemorandumInterinato' => 'nullable|string',
            'codigoMemorandumInterinato' => 'nullable|string',
            'citeRapInterinato' => 'nullable|string',
            'codigoRapInterinato' => 'nullable|string',
            'fchMemorandumRapInterinato' => 'nullable|date',
            'fchInicioInterinato' => 'nullable|date',
            'fchFinInterinato' => 'nullable|date',
            'totalDiasInterinato' => 'nullable|integer',
            'periodoInterinato' => 'nullable|string',
            'createdBy' => 'nullable|integer',
            'tipoNotaInformeMinutaInterinato' => 'nullable|string',
            'observacionesInterinato' => 'nullable|string',
            'sayriInterinato' => 'nullable|string',
        ]);

        $titularPuestoNuevoId = null;
        $titularPuestoActualId = null;

        if ($request->has('puestoNuevoId')) {
            $puestoNuevo = Puesto::findOrFail($request->puestoNuevoId);
            if ($puestoNuevo->estado_id == 2) {
                $titularPuestoNuevoId = $puestoNuevo->persona_actual_id;
            }
        }

        if ($request->has('puestoActualId')) {
            $puestoActual = Puesto::findOrFail($request->puestoActualId);
            if ($puestoActual->estado_id == 2) {
                $titularPuestoActualId = $puestoActual->persona_actual_id;
            }
        }

        $interinato = Interinato::create([
            'proveido_tramite_interinato' => $validatedData['proveidoTramiteInterinato'],
            'cite_nota_informe_minuta_interinato' => $validatedData['citeNotaInformeMinutaInterinato'],
            'fch_cite_nota_inf_minuta_interinato' => $validatedData['fchCiteNotaInfMinutaInterinato'],
            'puesto_nuevo_id' => $validatedData['puestoNuevoId'],
            'titular_puesto_nuevo_id' => $titularPuestoNuevoId,
            'puesto_actual_id' => $validatedData['puestoActualId'],
            'titular_puesto_actual_id' => $titularPuestoActualId,
            'cite_informe_interinato' => $validatedData['citeInformeInterinato'],
            'fojas_informe_interinato' => $validatedData['fojasInformeInterinato'],
            'cite_memorandum_interinato' => $validatedData['citeMemorandumInterinato'],
            'codigo_memorandum_interinato' => $validatedData['codigoMemorandumInterinato'],
            'cite_rap_interinato' => $validatedData['citeRapInterinato'],
            'codigo_rap_interinato' => $validatedData['codigoRapInterinato'],
            'fch_memorandum_rap_interinato' => $validatedData['fchMemorandumRapInterinato'],
            'fch_inicio_interinato' => $validatedData['fchInicioInterinato'],
            'fch_fin_interinato' => $validatedData['fchFinInterinato'],
            'total_dias_interinato' => $validatedData['totalDiasInterinato'],
            'periodo_interinato' => $validatedData['periodoInterinato'],
            'created_by' => $validatedData['createdBy'],
            'tipo_nota_informe_minuta_interinato' => $validatedData['tipoNotaInformeMinutaInterinato'],
            'observaciones_interinato' => $validatedData['observacionesInterinato'],
            'sayri_interinato' => $validatedData['sayriInterinato'],
            'estado' => 0,
        ]);

        // $fchInicioInterinato = Carbon::parse($validatedData['fchInicioInterinato']);
        // $fchFinInterinato = Carbon::parse($validatedData['fchFinInterinato']);
        // $fechaActual = Carbon::now();

        // if ($fchInicioInterinato->toDateString() === $fechaActual->toDateString()) {
        //     if ($request->has('puestoActualId') && $request->has('puestoNuevoId')) {
        //         $puestoActual = Puesto::find($request->puestoActualId);
        //         $puestoNuevo = Puesto::find($request->puestoNuevoId);

        //         if ($puestoActual && $puestoNuevo) {
        //             $puestoNuevo->persona_actual_id = $puestoActual->persona_actual_id;
        //             $puestoNuevo->denominacion_puesto = $puestoActual->denominacion_puesto . ' a.i.';
        //             $puestoNuevo->estado_id = 2;
        //             $puestoNuevo->save();

        //             $puestoActual->persona_actual_id = null;
        //             $puestoActual->estado_id = 1;
        //             $puestoActual->save();
        //         } else {
        //             throw new \Exception('No se encontraron los puestos especificados.');
        //         }
        //     } else {
        //         throw new \Exception('Los parámetros "puestoActualId" y "puestoNuevoId" son requeridos.');
        //     }
        // }

        // if ($fchFinInterinato->toDateString() === $fechaActual->toDateString()) {
        //     if ($request->has('puestoActualId') && $request->has('puestoNuevoId')) {
        //         $puestoActual = Puesto::find($request->puestoActualId);
        //         $puestoNuevo = Puesto::find($request->puestoNuevoId);
        //         if ($puestoActual && $puestoNuevo) {
        //             $puestoNuevo->persona_actual_id = $titularPuestoNuevoId;
        //             $puestoNuevo->denominacion_puesto = str_replace(' a.i.', '', $puestoActual->denominacion_puesto);

        //             if (!is_null($titularPuestoNuevoId) && $titularPuestoNuevoId !== '') {
        //                 $puestoNuevo->estado_id = 2;
        //             } else {
        //                 $puestoNuevo->estado_id = 1;
        //             }
        //             $puestoNuevo->save();

        //             $puestoActual->persona_actual_id = $titularPuestoActualId;
        //             if (!is_null($titularPuestoActualId) && $titularPuestoActualId !== '') {
        //                 $puestoActual->estado_id = 2;
        //             } else {
        //                 $puestoActual->estado_id = 1;
        //             }
        //             $puestoActual->save();
        //         } else {
        //             throw new \Exception('No se encontraron los puestos especificados.');
        //         }
        //     } else {
        //         throw new \Exception('Los parámetros "puestoActualId" y "puestoNuevoId" son requeridos.');
        //     }
        // }

        $interinato->save();

        $interinato->actualizarInterinatoDestino();

        return response()->json(['message' => 'Interinato creado correctamente', 'data' => $interinato], 200);
    }

    public function listarInterinatos(Request $request)
    {
        $limit = $request->input('limit', 1000);
        $page = $request->input('page', 0);

        $query = Interinato::with([
            'personaNuevo',
            'personaActual',
            'puestoNuevo:id_puesto,item_puesto,denominacion_puesto,departamento_id',
            'puestoNuevo.departamento:id_departamento,nombre_departamento,gerencia_id',
            'puestoNuevo.departamento.gerencia:id_gerencia,nombre_gerencia',
            'puestoActual:id_puesto,item_puesto,denominacion_puesto,departamento_id',
            'puestoActual.departamento:id_departamento,nombre_departamento,gerencia_id',
            'puestoActual.departamento.gerencia:id_gerencia,nombre_gerencia',
            'usuarioCreador',
            'usuarioModificador'
        ]);

        $incorporaciones = $query->paginate($limit, ['*'], 'page', $page);

        return $this->sendPaginated($incorporaciones);
    }

    public function byFiltrosInterinatos(Request $request)
    {
        $limit = $request->input('limit');
        $page = $request->input('page', 0);

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
}

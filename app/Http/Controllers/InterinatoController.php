<?php

namespace App\Http\Controllers;

use App\Models\Interinato;
use App\Models\Puesto;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;



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
            'created_by_interinato' => $validatedData['createdBy'],
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
        $limit = $request->input('limit');
        $page = $request->input('page', 1);

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
        ])->orderBy('created_at', 'desc');

        $interinatos = $query->paginate($limit, ['*'], 'page', $page);

        return $this->sendPaginated($interinatos);
    }

    /* public function byFiltrosInterinatos(Request $request)
    {
        $limit = $request->input('limit');
        $page = $request->input('page', 1);

        $itemNombre = $request->input('query.itemNombre');

        $query = Interinato::query()
            ->leftJoin('dde_puestos as puestoNuevo', 'dde_interinatos.puesto_nuevo_id', '=', 'puestoNuevo.id_puesto')
            ->leftJoin('dde_puestos as puestoActual', 'dde_interinatos.puesto_actual_id', '=', 'puestoActual.id_puesto')

            ->leftJoin('dde_personas as titular_nuevo', 'dde_interinatos.titular_puesto_nuevo_id', '=', 'titular_nuevo.id_persona')
            ->leftJoin('dde_personas as titular_actual', 'dde_interinatos.titular_puesto_actual_id', '=', 'titular_actual.id_persona');

        if (!empty($itemNombre)) {
            $query->where(function ($query) use ($itemNombre) {
                $query->where('puestoNuevo.item_puesto', $itemNombre);
                    //->orWhere('titular_nuevo.nombre_persona', 'LIKE', "%{$itemNombre}%")
                    
                    //->orWhere('titular_nuevo.primer_apellido_persona', 'LIKE', "%{$itemNombre}%")
                   // ->orWhere('titular_nuevo.segundo_apellido_persona', 'LIKE', "%{$itemNombre}%");
            });
        }

        $query->select([
           // 'titular_nuevo.nombre_persona as nombrePersona',
            //'titular_nuevo.primer_apellido_persona as primerApellidoPersona',
            //'titular_nuevo.segundo_apellido_persona as segundoApellidoPersona',
            'dde_interinatos.id_interinato as idInterinato',

            'puestoNuevo.id_puesto as idPuestoNuevo',
            'puestoNuevo.item_puesto as itemPuestoNuevo',
            'puestoNuevo.denominacion_puesto as denominacionPuestoNuevo',
            
            //'puestoNuevo.nombre_departamento as departamento',

            'puestoActual.id_puesto as idPuestoActual',
            'puestoActual.item_puesto as itemPuestoActual',
            'puestoActual.denominacion_puesto as denominacionPuestoActual',
          // 'departamentoActual.nombre_departamento as departamentoActual',
          //  'puestoActual.segundo_apellido_persona as segundoApellidoPersonaActual'

        ]);

        $query->orderBy('dde_interinatos.created_at', 'desc');

        $personaPuestos = $query->paginate($limit, ['*'], 'page', $page);

        return response()->json($personaPuestos);
    }*/

    public function byFiltrosInterinatos(Request $request)
    {
        $params = $request->all();

        $limit = $params['limit'];
        $page = $params['page'] ?? 1;
        $puestoPersona = $params['puestoPersona'] ?? null;

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
        ])->orderBy('created_at', 'desc');

        if (!empty($puestoPersona)) {
            $query->where(function ($query) use ($puestoPersona) {
                if (is_numeric($puestoPersona)) {
                    $query->whereHas('puestoNuevo', function ($query) use ($puestoPersona) {
                        $query->where('item_puesto', $puestoPersona);
                    });
                }

                $query->orWhereHas('personaActual', function ($query) use ($puestoPersona) {
                    $query->where(function ($query) use ($puestoPersona) {
                        $query->whereRaw("CONCAT(nombre_persona, ' ', primer_apellido_persona, ' ', segundo_apellido_persona) LIKE ?", ['%' . $puestoPersona . '%']);
                    });
                });
            });
        }

        $interinatos = $query->paginate($limit, ['*'], 'page', $page);

        return $this->sendPaginated($interinatos);
    }

    public function mostrarModificarInterinato($id)
    {
        $interinato = Interinato::with([
            'puestoNuevo.departamento.gerencia',
            'puestoNuevo.persona_actual',  
            'puestoActual.departamento', 'puestoActual.persona_actual', 
            'personaNuevo', 'personaActual'
        ])->findOrFail($id);
        return response()->json($interinato);
    }

    public function modificarInterinato(Request $request, $id)
    {
        $interinato = Interinato::findOrFail($id);

        $interinato->fill($request->all());

        $interinato->save();

        return response()->json(['message' => 'Interinato actualizado correctamente', 'data' => $interinato]);
    }
}

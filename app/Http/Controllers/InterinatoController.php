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
        ]);

        $fchInicioInterinato = Carbon::parse($validatedData['fchInicioInterinato']);
        $fchFinInterinato = Carbon::parse($validatedData['fchFinInterinato']);
        $fechaActual = Carbon::now();

        if ($fchInicioInterinato->toDateString() === $fechaActual->toDateString()) {
            if ($request->has('puestoActualId') && $request->has('puestoNuevoId')) {
                $puestoActual = Puesto::find($request->puestoActualId);
                $puestoNuevo = Puesto::find($request->puestoNuevoId);
                
                if ($puestoActual && $puestoNuevo) {
                    $puestoNuevo->persona_actual_id = $puestoActual->persona_actual_id;
                    $puestoNuevo->denominacion_puesto = $puestoActual->denominacion_puesto . ' a.i.';
                    $puestoNuevo->estado_id = 2;
                    $puestoNuevo->save();
        
                    $puestoActual->persona_actual_id = null;
                    $puestoActual->estado_id = 1;
                    $puestoActual->save();
                } else {
                    throw new \Exception('No se encontraron los puestos especificados.');
                }
            } else {
                throw new \Exception('Los parámetros "puestoActualId" y "puestoNuevoId" son requeridos.');
            }
        }
        
        if ($fchFinInterinato->toDateString() === $fechaActual->toDateString()) {
            if ($request->has('puestoActualId') && $request->has('puestoNuevoId')) {
                $puestoActual = Puesto::find($request->puestoActualId);
                $puestoNuevo = Puesto::find($request->puestoNuevoId);
                if ($puestoActual && $puestoNuevo) {
                    $puestoNuevo->persona_actual_id = $titularPuestoNuevoId;
                    $puestoNuevo->denominacion_puesto = str_replace(' a.i.', '', $puestoActual->denominacion_puesto);
                    
                    if (!is_null($titularPuestoNuevoId) && $titularPuestoNuevoId !== '') {
                        $puestoNuevo->estado_id = 2;
                    } else {
                        $puestoNuevo->estado_id = 1;
                    }
                    $puestoNuevo->save();
        
                    $puestoActual->persona_actual_id = $titularPuestoActualId;
                    if (!is_null($titularPuestoNuevoId) && $titularPuestoNuevoId !== '') {
                        $puestoNuevo->estado_id = 2;
                    } else {
                        $puestoNuevo->estado_id = 1;
                    }
                    $puestoActual->save();
                } else {
                    throw new \Exception('No se encontraron los puestos especificados.');
                }
            } else {
                throw new \Exception('Los parámetros "puestoActualId" y "puestoNuevoId" son requeridos.');
            }
        }        

        $interinato->save();

        return response()->json(['message' => 'Interinato creado correctamente', 'data' => $interinato], 200);
    }
}

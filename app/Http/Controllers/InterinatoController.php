<?php

namespace App\Http\Controllers;

use App\Imports\InterinatoDataImport;
use App\Models\Interinato;
use App\Models\Puesto;
use Dotenv\Exception\InvalidFileException as ExceptionInvalidFileException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Exceptions\InvalidFileException;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use Maatwebsite\Excel\Facades\Excel;


class InterinatoController extends Controller
{

    public function crearInterinato(Request $request)
    {
        $validatedData = $request->validate([
            'idInterinato' => 'nullable|integer',
            'puestoNuevoId' => 'nullable|integer',
            'puestoActualId' => 'nullable|integer',
            'proveidoTramiteInterinato' => 'nullable|string',
            'citeNotaInformeMinutaInterinato' => 'nullable|string',
            'fchCiteNotaInfMinutaInterinato' => 'nullable|date',
            'citeInformeInterinato' => 'nullable|string',
            'fojasInformeInterinato' => 'nullable|string',
            'citeMemorandumInterinato' => 'nullable|string',
            'codigoMemorandumInterinato' => 'nullable|string',
            'citeRapInterinato' => 'nullable|string',
            'codigoRapInterinato' => 'nullable|string',
            'fchMemorandumRapInterinato' => 'nullable|date',

            'totalDiasInterinato' => 'nullable|integer',
            'periodoInterinato' => 'nullable|string',
            'tipoNotaInformeMinutaInterinato' => 'nullable|string',
            'observacionesInterinato' => 'nullable|string',
            'sayriInterinato' => 'nullable|string',
            'createdByInterinato' => 'nullable|integer',


            'puestoNuevoId' => 'nullable|integer',
            'puestoActualId' => 'nullable|integer',
            'fchInicioInterinato' => 'nullable|date',
            'fchFinInterinato' => 'nullable|date',
        ]);

        if ($request->has('puestoActualId') && $request->fchInicioInterinato && $request->fchFinInterinato) {
            $puestoActualId = $request->puestoActualId;
            $fchInicio = $request->fchInicioInterinato;
            $fchFin = $request->fchFinInterinato;

            $interinatoExistente = Interinato::where('puesto_actual_id', $puestoActualId)
                ->where(function ($query) use ($fchInicio, $fchFin) {
                    $query->whereBetween('fch_inicio_interinato', [$fchInicio, $fchFin])
                        ->orWhereBetween('fch_fin_interinato', [$fchInicio, $fchFin])
                        ->orWhere(function ($query) use ($fchInicio, $fchFin) {
                            $query->where('fch_inicio_interinato', '<=', $fchInicio)
                                ->where('fch_fin_interinato', '>=', $fchFin);
                        });
                })
                ->exists();

            if ($interinatoExistente) {
                return response()->json(['message' => 'El puesto actual ya está asignado a otro interinato en el mismo período.'], 400);
            }
        }

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
            'created_by_interinato' => $validatedData['createdByInterinato'],
            'tipo_nota_informe_minuta_interinato' => $validatedData['tipoNotaInformeMinutaInterinato'],
            'observaciones_interinato' => $validatedData['observacionesInterinato'],
            'sayri_interinato' => $validatedData['sayriInterinato'],
            'estado_designacion_interinato' => 0,
        ]);

        $interinato->save();

        return response()->json(['message' => 'Interinato creado correctamente', 'data' => $interinato], 200);
    }

    public function listarInterinatos(Request $request)
    {
        $personaInterinato = $request->input('query');
        $limit = $request->input('limit');
        $page = $request->input('page');

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

        $query->where('estado_designacion_interinato', 0);

        if (!empty($personaInterinato)) {
            $query->where(function ($query) use ($personaInterinato) {
                $query->whereHas('puestoNuevo', function ($query) use ($personaInterinato) {
                    $query->where('denominacion_puesto', 'LIKE', '%' . $personaInterinato . '%');
                })
                    ->orWhereHas('personaActual', function ($query) use ($personaInterinato) {
                        $query->where(function ($query) use ($personaInterinato) {
                            $query->whereRaw("CONCAT(nombre_persona, ' ', primer_apellido_persona, ' ', segundo_apellido_persona) LIKE ?", ['%' . $personaInterinato . '%']);
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
            'puestoActual.departamento.gerencia',
            'puestoActual.persona_actual',
            'personaNuevo',
            'personaActual'
        ])->findOrFail($id);
        return response()->json($interinato);
    }

    public function modificarInterinato(Request $request)
    {
        $validatedData = $request->validate([
            'idInterinato' => 'nullable|integer',
            'proveidoTramiteInterinato' => 'nullable|string',
            'citeNotaInformeMinutaInterinato' => 'nullable|string',
            'fchCiteNotaInfMinutaInterinato' => 'nullable|date',
            'fchMemorandumRapInterinato' => 'nullable|date',
            'citeInformeInterinato' => 'nullable|string',
            'fojasInformeInterinato' => 'nullable|string',
            'citeMemorandumInterinato' => 'nullable|string',
            'codigoMemorandumInterinato' => 'nullable|string',
            'citeRapInterinato' => 'nullable|string',
            'codigoRapInterinato' => 'nullable|string',
            'fchInicioInterinato' => 'nullable|date',
            'fchFinInterinato' => 'nullable|date',
            'totalDiasInterinato' => 'nullable|integer',
            'periodoInterinato' => 'nullable|string',
            'tipoNotaInformeMinutaInterinato' => 'nullable|string',
            'sayriInterinato' => 'nullable|string',
            'observacionesInterinato' => 'nullable|string',
            'modifiedByInterinato' => 'nullable|integer',
        ]);

        $id = $validatedData['idInterinato'];

        $updated = Interinato::where('id_interinato', $id)->update([
            'proveido_tramite_interinato' => $validatedData['proveidoTramiteInterinato'],
            'cite_nota_informe_minuta_interinato' => $validatedData['citeNotaInformeMinutaInterinato'],
            'fch_cite_nota_inf_minuta_interinato' => $validatedData['fchCiteNotaInfMinutaInterinato'],
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
            'created_by_interinato' => $validatedData['createdByInterinato'],
            'tipo_nota_informe_minuta_interinato' => $validatedData['tipoNotaInformeMinutaInterinato'],
            'observaciones_interinato' => $validatedData['observacionesInterinato'],
            'sayri_interinato' => $validatedData['sayriInterinato'],
            'estado_designacion_interinato' => 0,
        ]);
        if ($updated) {
            return response()->json(['message' => 'Interinato actualizado correctamente']);
        } else {
            return response()->json(['message' => 'No se encontró el interinato para actualizar'], 404);
        }
    }

    public function darBajaInterinato($interinatoId)
    {
        $interinato = Interinato::find($interinatoId);

        if (!$interinato) {
            return response()->json(['message' => 'Interinato no encontrado.'], 404);
        }

        $interinato->estado_designacion_interinato = 1;

        $interinato->save();

        return response()->json(['message' => 'Interinato dado de baja exitosamente.'], 200);
    }
}

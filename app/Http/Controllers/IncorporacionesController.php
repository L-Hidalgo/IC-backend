<?php

namespace App\Http\Controllers;

use App\Models\Incorporacion;
use App\Models\Puesto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportEvaluacionExport;
use App\Exports\ReportTrimestralExport;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Persona;

class IncorporacionesController extends Controller
{

    public function crearActualizarIncorporacion(Request $request)
    {
        $validatedData = $request->validate([
            'idIncorporacion' => 'nullable|integer',
            'puestoNuevoId' => 'nullable|integer',
            'puestoActualId' => 'nullable|integer',
            'personaId' => 'nullable|integer',
            'createdByIncorporacion' => 'nullable|integer',
            'modifiedByIncorporacion' => 'nullable|integer',
            'obsEvaluacionIncorporacion' => 'nullable|string',
            'detalleObsEvaluacionIncorporacion' => 'nullable|string',
            'expEvaluacionIncorporacion' => 'nullable|string',
            'fchObsEvaluacionIncorporacion'  => 'nullable|string',
            'cumpleExpProfesionalIncorporacion' => 'nullable|integer',
            'cumpleExpEspecificaIncorporacion' => 'nullable|integer',
            'cumpleExpMandoIncorporacion' => 'nullable|integer',
            'cumpleFormacionIncorporacion' => 'nullable|integer',
            'hpIncorporacion' => 'nullable|string',
            'nTramiteIncorporacion' => 'nullable|string',
            'citeInformeIncorporacion' => 'nullable|string',
            'fchInformeIncorporacion' => 'nullable|string',
            'fchIncorporacion' => 'nullable|string',
            'citeNotaMinutaIncorporacion' => 'nullable|string',
            'codigoNotaMinutaIncorporacion' => 'nullable|string',
            'fchNotaMinutaIncorporacion' => 'nullable|string',
            'fchRecepcionNotaIncorporacion' => 'nullable|string',
            'citeMemorandumIncorporacion' => 'nullable|string',
            'codigoMemorandumIncorporacion' => 'nullable|string',
            'fchMemorandumIncorporacion' => 'nullable|string',
            'citeRapIncorporacion' => 'nullable|string',
            'codigoRapIncorporacion' => 'nullable|string',
            'fchRapIncorporacion' => 'nullable|string',
        ]);

        if (!isset($validatedData['puestoNuevoId']) || !isset($validatedData['personaId'])) {
            return response()->json(['error' => 'Tanto puesto como persona deben estar presentes para realizar la incorporación.'], 400);
        }

        $puesto = Puesto::find($validatedData['puestoNuevoId']);

        if (!$puesto) {
            return response()->json(['error' => 'El puesto especificado no existe.'], 400);
        }

        if (isset($validatedData['puestoActualId'])) {
            $puestoActual = Puesto::find($validatedData['puestoActualId']);
            if ($puestoActual && $puestoActual->persona_actual_id == $validatedData['personaId']) {
                $puestoActual->persona_actual_id = null;
                $puestoActual->estado_id = 1; // desocupado
                $puestoActual->save();
            }
        }

        $puesto->persona_actual_id = $validatedData['personaId'];
        $puesto->estado_id = 2; // ocupado
        $puesto->save();

        $incorporacion = Incorporacion::where('persona_id', $validatedData['personaId'])
            ->where('puesto_nuevo_id', $validatedData['puestoNuevoId'])
            ->first() ?? new Incorporacion();

        if (!$incorporacion->exists) {
            $incorporacion->codigo_nota_minuta_incorporacion = '022400000';
            $incorporacion->codigo_memorandum_incorporacion = '08240000';
            $incorporacion->codigo_rap_incorporacion = '032400000';
        }

        $incorporacion->fill([
            'puesto_nuevo_id' => $validatedData['puestoNuevoId'],
            'puesto_actual_id' => $validatedData['puestoActualId'] ?? $incorporacion->puesto_actual_id,
            'persona_id' => $validatedData['personaId'],
            'created_by_incorporacion' => $validatedData['createdByIncorporacion'] ?? $incorporacion->created_by_incorporacion,
            'modified_by_incorporacion' => $validatedData['modifiedByIncorporacion'] ?? $incorporacion->modified_by_incorporacion,
            'obs_evaluacion_incorporacion' => $validatedData['obsEvaluacionIncorporacion'] ?? $incorporacion->obs_evaluacion_incorporacion,
            'detalle_obs_evaluacion_incorporacion' => $validatedData['detalleObsEvaluacionIncorporacion'] ?? $incorporacion->detalle_obs_evaluacion_incorporacion,
            'exp_evaluacion_incorporacion' => $validatedData['expEvaluacionIncorporacion'] ?? $incorporacion->exp_evaluacion_incorporacion,
            'fch_obs_evaluacion_incorporacion' => isset($validatedData['fchObsEvaluacionIncorporacion']) ? Carbon::parse($validatedData['fchObsEvaluacionIncorporacion'])->format('Y-m-d') : $incorporacion->fch_obs_evaluacion_incorporacion,
            'cumple_exp_profesional_incorporacion' => $validatedData['cumpleExpProfesionalIncorporacion'] ?? $incorporacion->cumple_exp_profesional_incorporacion,
            'cumple_exp_especifica_incorporacion' => $validatedData['cumpleExpEspecificaIncorporacion'] ?? $incorporacion->cumple_exp_especifica_incorporacion,
            'cumple_exp_mando_incorporacion' => $validatedData['cumpleExpMandoIncorporacion'] ?? $incorporacion->cumple_exp_mando_incorporacion,
            'cumple_formacion_incorporacion' => $validatedData['cumpleFormacionIncorporacion'] ?? $incorporacion->cumple_formacion_incorporacion,
            'hp_incorporacion' => $validatedData['hpIncorporacion'] ?? $incorporacion->hp_incorporacion,
            'n_tramite_incorporacion' => $validatedData['nTramiteIncorporacion'] ?? $incorporacion->n_tramite_incorporacion,
            'cite_informe_incorporacion' => $validatedData['citeInformeIncorporacion'] ?? $incorporacion->cite_informe_incorporacion,
            'fch_informe_incorporacion' => isset($validatedData['fchInformeIncorporacion']) ? Carbon::parse($validatedData['fchInformeIncorporacion'])->format('Y-m-d') : $incorporacion->fch_informe_incorporacion,
            'fch_incorporacion' => isset($validatedData['fchIncorporacion']) ? Carbon::parse($validatedData['fchIncorporacion'])->format('Y-m-d') : $incorporacion->fch_incorporacion,
            'cite_nota_minuta_incorporacion' => $validatedData['citeNotaMinutaIncorporacion'] ?? $incorporacion->cite_nota_minuta_incorporacion,
            'codigo_nota_minuta_incorporacion' => $validatedData['codigoNotaMinutaIncorporacion'] ?? $incorporacion->codigo_nota_minuta_incorporacion,
            'fch_nota_minuta_incorporacion' => isset($validatedData['fchNotaMinutaIncorporacion']) ? Carbon::parse($validatedData['fchNotaMinutaIncorporacion'])->format('Y-m-d') : $incorporacion->fch_nota_minuta_incorporacion,
            'fch_recepcion_nota_incorporacion' => isset($validatedData['fchRecepcionNotaIncorporacion']) ? Carbon::parse($validatedData['fchRecepcionNotaIncorporacion'])->format('Y-m-d') : $incorporacion->fch_recepcion_nota_incorporacion,
            'cite_memorandum_incorporacion' => $validatedData['citeMemorandumIncorporacion'] ?? $incorporacion->cite_memorandum_incorporacion,
            'codigo_memorandum_incorporacion' => $validatedData['codigoMemorandumIncorporacion'] ?? $incorporacion->codigo_memorandum_incorporacion,
            'fch_memorandum_incorporacion' => isset($validatedData['fchMemorandumIncorporacion']) ? Carbon::parse($validatedData['fchMemorandumIncorporacion'])->format('Y-m-d') : $incorporacion->fch_memorandum_incorporacion,
            'cite_rap_incorporacion' => $validatedData['citeRapIncorporacion'] ?? $incorporacion->cite_rap_incorporacion,
            'codigo_rap_incorporacion' => $validatedData['codigoRapIncorporacion'] ?? $incorporacion->codigo_rap_incorporacion,
            'fch_rap_incorporacion' => isset($validatedData['fchRapIncorporacion']) ? Carbon::parse($validatedData['fchRapIncorporacion'])->format('Y-m-d') : $incorporacion->fch_rap_incorporacion,
        ]);

        $estado_incorporacion = 1;

        $allFieldsPresentLevel2 = [
            'idIncorporacion',
            'puestoNuevoId',
            'personaId',
            'obsEvaluacionIncorporacion',
            'fchObsEvaluacionIncorporacion',
            'cumpleExpProfesionalIncorporacion',
            'cumpleExpEspecificaIncorporacion',
            'cumpleExpMandoIncorporacion',
            'cumpleFormacionIncorporacion',
            'hpIncorporacion',
            'nTramiteIncorporacion',
            'citeInformeIncorporacion',
            'fchInformeIncorporacion',
            'fchIncorporacion',
            'citeNotaMinutaIncorporacion',
            'fchNotaMinutaIncorporacion'
        ];

        $allFieldsPresentLevel3 = array_merge($allFieldsPresentLevel2, [
            'citeMemorandumIncorporacion',
            'codigoMemorandumIncorporacion',
            'fchMemorandumIncorporacion',
            'citeRapIncorporacion',
            'codigoRapIncorporacion',
            'fchRapIncorporacion'
        ]);

        if (empty(array_diff($allFieldsPresentLevel2, array_keys($validatedData)))) {
            $estado_incorporacion = 2;
        }

        if (empty(array_diff($allFieldsPresentLevel3, array_keys($validatedData)))) {
            $estado_incorporacion = 3;
        }
 
        $incorporacion->estado_incorporacion = $estado_incorporacion;
        $incorporacion->save();

        return $this->sendObject($incorporacion, 'Datos registrados exitosamente!!');
    }


    public function darBajaIncorporacion($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);

        if (!$incorporacion) {
            return response()->json(['error' => 'Incorporación no encontrada'], 404);
        }

        $puestoActualAnterior = $incorporacion->puesto_actual_id;
        $puestoNuevoAnterior = $incorporacion->puesto_nuevo_id;

        $puestoActual = Puesto::find($puestoActualAnterior);
        if ($puestoActual) {
            if ($puestoActual->persona_anterior_id === null) {
                $puestoActual->persona_actual_id = $puestoActual->persona_anterior_id;
                $puestoActual->estado_id = 1;
            } else {
                $puestoActual->persona_actual_id = $puestoActual->persona_anterior_id;
                $puestoActual->persona_anterior_id = null;
                $puestoActual->estado_id = 2;
            }
            $puestoActual->save();
        }

        $puestoNuevo = Puesto::find($puestoNuevoAnterior);
        if ($puestoNuevo) {
            if ($puestoNuevo->persona_anterior_id === null) {
                $puestoNuevo->persona_actual_id = $puestoNuevo->persona_anterior_id;
                $puestoNuevo->estado_id = 1;
            } else {
                $puestoNuevo->persona_actual_id = $puestoNuevo->persona_anterior_id;
                $puestoNuevo->persona_anterior_id = null;
                $puestoNuevo->estado_id = 2;
            }
            $puestoNuevo->save();
        }

        $incorporacion->estado_incorporacion = 3;

        $incorporacion->save();

        return response()->json(['message' => 'Incorporación dada de baja exitosamente'], 200);
    }

    public function listarIncorporaciones(Request $request)
    {
        $limit = $request->input('limit', 1000);
        $page = $request->input('page', 0);

        $encargado = $request->input('query.encargado');
        $nombreCompletoPersona = $request->input('query.nombreCompletoPersona');
        $tipoIncorporacion = $request->input('query.tipoIncorporacion');
        $fechaInicio = $request->input('query.fechaInicio');
        $fechaFin = $request->input('query.fechaFin');


        $query = Incorporacion::with([
            'persona',
            'puesto_nuevo:id_puesto,item_puesto,denominacion_puesto,departamento_id',
            'puesto_nuevo.departamento:id_departamento,nombre_departamento,gerencia_id',
            'puesto_nuevo.departamento.gerencia:id_gerencia,nombre_gerencia',
            'puesto_actual:id_puesto,item_puesto,denominacion_puesto,departamento_id',
            'puesto_actual.departamento:id_departamento,nombre_departamento,gerencia_id',
            'puesto_actual.departamento.gerencia:id_gerencia,nombre_gerencia',
            'createdBy',
            'modifiedBy',
        ])->orderBy('id_incorporacion', 'desc');

        if ($encargado) {
            $query->whereHas('createdBy', function ($q) use ($encargado) {
                $q->whereRaw("name LIKE ?", ['%' . $encargado . '%']);
            });
        }

        if ($nombreCompletoPersona) {
            $query->whereHas('persona', function ($q) use ($nombreCompletoPersona) {
                $q->whereRaw("CONCAT(nombre_persona, ' ', primer_apellido_persona, ' ', segundo_apellido_persona) LIKE ?", ['%' . $nombreCompletoPersona . '%']);
            });
        }

        if ($tipoIncorporacion == 1) {
            $query->whereNotNull('puesto_nuevo_id')->whereNull('puesto_actual_id');
        } elseif ($tipoIncorporacion == 2) {
            $query->whereNotNull('puesto_nuevo_id')->whereNotNull('puesto_actual_id');
        }

        if ($fechaInicio) {
            $query->whereDate('created_at', '>=', $fechaInicio);
        }

        if ($fechaFin) {
            $query->whereDate('created_at', '<=', $fechaFin);
        }

        $incorporaciones = $query->paginate($limit, ['*'], 'page', $page);

        return $this->sendPaginated($incorporaciones);
    }

    //formularios de evaluacion
    private function valoresComunesByEvaluation(TemplateProcessor $templateProcessor, Incorporacion $incorporacion)
    {
        $templateProcessor->setValue('persona.nombreCompleto', mb_strtoupper($incorporacion->persona->nombre_persona) . ' ' . mb_strtoupper($incorporacion->persona->primer_apellido_persona) . ' ' . mb_strtoupper($incorporacion->persona->segundo_apellido_persona));
        $templateProcessor->setValue('persona.ci', $incorporacion->persona->ci_persona);
        $templateProcessor->setValue('persona.exp', $incorporacion->persona->exp_persona);

        $templateProcessor->setValue('puestoNuevo.item', $incorporacion->puesto_nuevo->item_puesto);

        $templateProcessor->setValue('puestoNuevo.gerencia', mb_strtoupper($incorporacion->puesto_nuevo->departamento->gerencia->nombre_gerencia));

        $templateProcessor->setValue('puestoNuevo.departamento', mb_strtoupper($incorporacion->puesto_nuevo->departamento->nombre_departamento));

        $templateProcessor->setValue('puestoNuevo.denominacion', mb_strtoupper($incorporacion->puesto_nuevo->denominacion_puesto));

        $salarioFormateado = number_format($incorporacion->puesto_nuevo->salario_puesto / 1000, 3, '.', '');
        $templateProcessor->setValue('puestoNuevo.salario', $salarioFormateado);

        foreach ($incorporacion->puesto_nuevo->requisitos as $requisito) {
            if ($requisito) {
                $templateProcessor->setValue('puestoNuevo.formacionRequerida', $requisito->formacion_requisito);
                $templateProcessor->setValue('puestoNuevo.experienciaProfesionalSegunCargo', $requisito->exp_cargo_requisito);
                $templateProcessor->setValue('puestoNuevo.experienciaRelacionadoAlArea', $requisito->exp_area_requisito);
                $templateProcessor->setValue('puestoNuevo.experienciaEnFuncionesDeMando', $requisito->exp_mando_requisito);
                break;
            }
        }

        $observacion = '';
        if ($incorporacion->obs_evaluacion_incorporacion === '1') {
            $observacion = 'CUMPLE';
        } elseif ($incorporacion->obs_evaluacion_incorporacion === '0') {
            $observacion = 'NO CUMPLE';
        } else {
            $observacion = 'NO REGISTRADO';
        }

        $templateProcessor->setValue('incorporacion.observacion', mb_strtoupper($observacion));
    }

    public function generarFormR0078($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);
        if (!isset($incorporacion)) {
            return response('', 404);
        }

        $disk = Storage::disk('form_templates');

        $pathTemplate = $disk->path('R-0078-01.docx');

        $templateProcessor = new TemplateProcessor($pathTemplate);

        $this->valoresComunesByEvaluation($templateProcessor, $incorporacion);

        $gradoAcademico = mb_strtoupper($incorporacion->persona->formacion[0]->gradoAcademico->nombre_grado_academico ?? '', 'UTF-8');
        $areaFormacion = mb_strtoupper($incorporacion->persona->formacion[0]->areaFormacion->nombre_area_formacion ?? '', 'UTF-8');
        if (empty($gradoAcademico) || empty($areaFormacion)) {
            $profesion = 'Registrar grado académico y área de formación';
        } else {
            $profesion = $gradoAcademico . ' EN ' . $areaFormacion;
        }
        $templateProcessor->setValue('persona.profesion', $profesion);

        $fechaNacimiento = Carbon::parse($incorporacion->persona->fch_nacimiento_persona);
        $edad = $fechaNacimiento->age;
        $templateProcessor->setValue('persona.edad', $edad . ' ' . 'AÑOS');

        $experiencia = $incorporacion->exp_evaluacion_incorporacion;
        if ($experiencia == 0) {
            $mensajeExperiencia = 'NO CUENTA CON EXPERIENCIA EN EL SERVICIO DE IMPUESTOS NACIONALES';
        } elseif ($experiencia == 1) {
            $mensajeExperiencia = 'SI CUENTA CON EXPERIENCIA EN EL SERVICIO DE IMPUESTOS NACIONALES';
        }
        $templateProcessor->setValue('incorporacion.experiencia', mb_strtoupper($mensajeExperiencia));


        $fileName = 'R-0078 ' . mb_strtoupper($incorporacion->persona->nombre_persona) . ' ' . mb_strtoupper($incorporacion->persona->primer_apellido_persona) . ' ' . mb_strtoupper($incorporacion->persona->segundo_apellido_persona) . ' ' . $incorporacion->descargas;

        $incorporacion->descargas++;

        $savedPath = $disk->path('generados/') . $fileName . '.docx';

        $templateProcessor->saveAs($savedPath);

        return response()->download($savedPath)->deleteFileAfterSend(true);
    }

    public function generarFormR1401($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);

        if (!isset($incorporacion)) {
            return response('', 404);
        }

        $disk = Storage::disk('form_templates');
        $pathTemplate = $disk->path('R-1401-01.docx');
        $templateProcessor = new TemplateProcessor($pathTemplate);

        $this->valoresComunesByEvaluation($templateProcessor, $incorporacion);

        $nombreGerencia = $incorporacion->puesto_nuevo->departamento->gerencia->nombre_gerencia;

        switch ($nombreGerencia) {
            case 'El Alto':
                $ubicacion = 'El Alto';
                break;
            case 'Cochabamba':
            case 'GRACO Cochabamba':
                $ubicacion = 'Cochabamba';
                break;
            case 'Quillacollo':
                $ubicacion = 'Quillacollo';
                break;
            case 'Santa Cruz I':
            case 'Santa Cruz II':
            case 'GRACO Santa Cruz':
                $ubicacion = 'Santa Cruz';
                break;
            case 'Montero':
                $ubicacion = 'Montero';
                break;
            case 'Chuquisaca':
                $ubicacion = 'Chuquisaca';
                break;
            case 'Tarija':
                $ubicacion = 'Tarija';
                break;
            case 'Yacuiba':
                $ubicacion = 'Yacuiba';
                break;
            case 'Oruro':
                $ubicacion = 'Oruro';
                break;
            case 'Potosí':
                $ubicacion = 'Potosí';
                break;
            case 'Beni':
                $ubicacion = 'Beni';
                break;
            case 'Pando':
                $ubicacion = 'Pando';
                break;
            default:
                $ubicacion = 'La Paz';
                break;
        }
        $templateProcessor->setValue('ubicacion', $ubicacion);

        $carbonFechaincorporacion = Carbon::parse($incorporacion->fch_incorporacion);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaincorporacion->locale('es_UY');
        $fechaincorporacionFormateada = $carbonFechaincorporacion->isoFormat('LL');
        $templateProcessor->setValue('fechaIncorporacion', $fechaincorporacionFormateada);

        $fileName = 'R-1401 ' . strtoupper($incorporacion->persona->nombre_persona) . ' ' . strtoupper($incorporacion->persona->primer_apellido_persona) . ' ' . strtoupper($incorporacion->persona->segundo_apellido_persona) . ' ' . $incorporacion->descargas;

        $incorporacion->descargas++;

        $savedPath = $disk->path('generados/') . $fileName . '.docx';

        $templateProcessor->saveAs($savedPath);

        return response()->download($savedPath)->deleteFileAfterSend(true);
    }

    public function generarFormR1023($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);
        if (!isset($incorporacion)) {
            return response('', 404);
        }

        $disk = Storage::disk('form_templates');

        $pathTemplate = $disk->path('R-1023-01-CambioItem.docx');

        $templateProcessor = new TemplateProcessor($pathTemplate);

        $this->valoresComunesByEvaluation($templateProcessor, $incorporacion);

        $profesionPersona = mb_strtoupper($incorporacion->persona->profesion_persona ?? '', 'UTF-8');

        if (empty($profesionPersona)) {
            $gradoAcademico = mb_strtoupper($incorporacion->persona->formacion[0]->gradoAcademico->nombre_grado_academico ?? '', 'UTF-8');
            $areaFormacion = mb_strtoupper($incorporacion->persona->formacion[0]->areaFormacion->nombre_area_formacion ?? '', 'UTF-8');

            if (empty($gradoAcademico) || empty($areaFormacion)) {
                $profesion = 'Registrar grado académico y área de formación';
            } else {
                $profesion = $gradoAcademico . ' EN ' . $areaFormacion;
            }

            $templateProcessor->setValue('persona.formacion', $profesion);
        } else {
            $templateProcessor->setValue('persona.formacion', $profesionPersona);
        }

        if (!$incorporacion->puesto_actual->funcionario->isEmpty()) {
            $fechaDesignacion = $incorporacion->puesto_actual->funcionario->first()->fch_inicio_sin_funcionario;
            $carbonFecha = Carbon::parse($fechaDesignacion);
            setlocale(LC_TIME, 'es_UY');
            $carbonFecha->locale('es_UY');
            $fechaFormateada = $carbonFecha->isoFormat('LL');
            $templateProcessor->setValue('puestoActual.fechaDeUltimaDesignacion', strtoupper($fechaFormateada));
        }

        $templateProcessor->setValue('puestoActual.item', $incorporacion->puesto_actual->item_puesto);

        $templateProcessor->setValue('puestoActual.gerencia', $incorporacion->puesto_actual->departamento->gerencia->nombre_gerencia);

        $templateProcessor->setValue('puestoActual.departamento', $incorporacion->puesto_actual->departamento->nombre_departamento);

        $templateProcessor->setValue('puestoActual.denominacion', $incorporacion->puesto_actual->denominacion_puesto);

        $salarioFormateado = number_format($incorporacion->puesto_nuevo->salario_puesto / 1000, 3, '.', '');
        $templateProcessor->setValue('puestoActual.salario', $salarioFormateado);

        $fileName = 'R-1023-01 ' . mb_strtoupper($incorporacion->persona->nombre_persona) . ' ' . mb_strtoupper($incorporacion->persona->primer_apellido_persona) . ' ' . mb_strtoupper($incorporacion->persona->segundo_apellido_persona) . ' ' . $incorporacion->descargas;

        $incorporacion->descargas++;

        $savedPath = $disk->path('generados/') . $fileName . '.docx';

        $templateProcessor->saveAs($savedPath);

        return response()->download($savedPath)->deleteFileAfterSend(true);
    }

    public function generarFormR1129($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);

        if (!isset($incorporacion)) {
            return response('', 404);
        }

        $disk = Storage::disk('form_templates');

        $pathTemplate = $disk->path('R-1129-01-CambioItem.docx'); // ruta de plantilla

        $templateProcessor = new TemplateProcessor($pathTemplate);

        $this->valoresComunesByEvaluation($templateProcessor, $incorporacion);

        $fileName = 'R-1129-01 ' . $incorporacion->persona->nombre_persona . ' ' . $incorporacion->persona->primer_apellido_persona . ' ' . $incorporacion->persona->segundo_apellido_persona . ' ' . $incorporacion->descargas;

        $incorporacion->descargas++;

        $savedPath = $disk->path('generados/') . $fileName . '.docx';

        $templateProcessor->saveAs($savedPath);

        return response()->download($savedPath)->deleteFileAfterSend(true);
    }

    public function generarFormR0980($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);

        if (!isset($incorporacion)) {
            return response('', 404);
        }

        $disk = Storage::disk('form_templates');
        if (isset($incorporacion->puesto_actual)) {
            $pathTemplate = $disk->path('R-0980-01.docx');
        } else {
            $pathTemplate = $disk->path('R-0980-01.docx');
        }

        $templateProcessor = new TemplateProcessor($pathTemplate);

        $carbonFechaInfo = Carbon::parse($incorporacion->fch_informe_incorporacion);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaInfo->locale('es_UY');
        $fechaInfoFormateada = $carbonFechaInfo->isoFormat('LL');
        $templateProcessor->setValue('incorporacion.fechaInfo', $fechaInfoFormateada);

        $this->valoresComunesByEvaluation($templateProcessor, $incorporacion);

        $gerencia = $incorporacion->puesto_nuevo->departamento->gerencia->nombre_gerencia;
        $gerenciasDepartamentos = array(
            "Gerencia Distrital La Paz I" => "el Departamento Administrativo y Recursos Humanos",
            "Gerencia Distrital La Paz II" => "la Administrativo y Recursos Humanos",
            "Gerencia GRACO La Paz" => "la Administrativo y Recursos Humanos",
            "Gerencia Distrital El Alto" => "la Administrativo y Recursos Humanos",
            "Gerencia Distrital Cochabamba" => "el Departamento Administrativo y Recursos Humanos",
            "Gerencia GRACO Cochabamba" => "el Departamento Administrativo y Recursos Humanos",
            "Gerencia Distrital Santa Cruz I" => "el Departamento Administrativo y Recursos Humanos",
            "Gerencia Distrital Santa Cruz II" => "la Administrativo y Recursos Humanos",
            "Gerencia GRACO Santa Cruz" => "la Administrativo y Recursos Humanos",
            "Gerencia Distrital Montero" => "la Administrativo y Recursos Humanos",
            "Gerencia Distrital Chuquisaca" => "la Administrativo y Recursos Humanos",
            "Gerencia Distrital Tarija" => "la Administrativo y Recursos Humanos",
            "Gerencia Distrital Yacuiba" => "la Administrativo y Recursos Humanos",
            "Gerencia Distrital Oruro" => "la Administrativo y Recursos Humanos",
            "Gerencia Distrital Potosí" => "la Administrativo y Recursos Humanos",
            "Gerencia Distrital Beni" => "la Administrativo y Recursos Humanos",
            "Gerencia Distrital Pando" => "la Administrativo y Recursos Humanos",
        );

        if (isset($gerenciasDepartamentos[$gerencia])) {
            $departamento = $gerenciasDepartamentos[$gerencia];
        } else {
            $departamento = "el Departamento de Dotación y Evaluación";
        }
        $templateProcessor->setValue('puestoNuevo.departamento', $departamento);

        $fileName = 'R-0980-01 ' . strtoupper($incorporacion->persona->nombre_persona) . ' ' . strtoupper($incorporacion->persona->primer_apellido_persona) . ' ' . strtoupper($incorporacion->persona->segundo_apellido_persona) . ' ' . $incorporacion->descargas;

        $incorporacion->descargas++;

        $savedPath = $disk->path('generados/') . $fileName . '.docx';

        $templateProcessor->saveAs($savedPath);

        return response()->download($savedPath)->deleteFileAfterSend(true);
    }

    //formularios de incorporacion
    private function valoresComunesByIncorporacion(TemplateProcessor $templateProcessor, Incorporacion $incorporacion)
    {

        $incorporacion->load('createdBy');

        if ($incorporacion->createdBy) {
            $nombreCompleto = $incorporacion->createdBy->name;
            $cargoUsuario = mb_strtoupper($incorporacion->createdBy->cargo, 'UTF-8');

            $templateProcessor->setValue('incorporacion.nombreUsuario', $nombreCompleto);
            $templateProcessor->setValue('incorporacion.cargoUsuario', $cargoUsuario);

            $partesNombre = explode(' ', $nombreCompleto);
            $iniciales = '';
            foreach ($partesNombre as $parte) {
                $iniciales .= substr($parte, 0, 1);
            }
            $templateProcessor->setValue('incorporacion.abrevNombreUsuario', $iniciales);
        } else {
            $templateProcessor->setValue('incorporacion.nombreUsuario', 'Usuario no disponible');
            $templateProcessor->setValue('incorporacion.cargoUsuario', 'Cargo no disponible');
            $templateProcessor->setValue('incorporacion.abrevNombreUsuario', 'N/A');
        }

        $carbonFechaInfo = Carbon::parse($incorporacion->fch_informe_incorporacion);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaInfo->locale('es_UY');
        $fechaInfoFormateada = $carbonFechaInfo->isoFormat('LL');
        $templateProcessor->setValue('incorporacion.fechaInforme', $fechaInfoFormateada);

        $templateProcessor->setValue('incorporacion.citeInforme', $incorporacion->cite_informe_incorporacion);

        $templateProcessor->setValue('incorporacion.hp', $incorporacion->hp_incorporacion);

        $templateProcessor->setValue('incorporacion.numeroTramite', $incorporacion->n_tramite_incorporacion);

        $templateProcessor->setValue('incorporacion.citeInfNotaMinuta', $incorporacion->cite_nota_minuta_incorporacion);

        $templateProcessor->setValue('incorporacion.codigoNotaMinuta', $incorporacion->codigo_nota_minuta_incorporacion);

        $carbonFechaNotaMinuta = Carbon::parse($incorporacion->fch_nota_minuta_incorporacion);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaNotaMinuta->locale('es_UY');
        $fechaNotaMinutaFormateada = $carbonFechaNotaMinuta->isoFormat('LL');
        $templateProcessor->setValue('incorporacion.fechaNotaMinuta', $fechaNotaMinutaFormateada);


        $carbonFechaRecepcion = Carbon::parse($incorporacion->fch_recepcion_nota_incorporacion);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaRecepcion->locale('es_UY');
        $fechaRecepcionFormateada = $carbonFechaRecepcion->isoFormat('LL');
        $templateProcessor->setValue('incorporacion.fechaRecepcion', $fechaRecepcionFormateada);

        $formacion = $incorporacion->persona->formacion->first();
        if ($formacion) {
            $respaldoFormacion = $formacion->pivot->con_respaldo_formacion ?? 'Valor predeterminado';
        } else {
            $respaldoFormacion = 'Valor predeterminado';
        }
        $templateProcessor->setValue('incorporacion.respaldoFormacion', $this->obtenerTextoSegunValorDeFormacion($respaldoFormacion));

        $templateProcessor->setValue('incorporacion.citeMemo', $incorporacion->cite_memorandum_incorporacion);

        $templateProcessor->setValue('incorporacion.citeRap', $incorporacion->cite_rap_incorporacion);

        $templateProcessor->setValue('persona.nombreCompleto', $incorporacion->persona->nombre_persona . ' ' . $incorporacion->persona->primer_apellido_persona . ' ' . $incorporacion->persona->segundo_apellido_persona);

        $templateProcessor->setValue('persona.grado', $incorporacion->persona->formacion[0]->gradoAcademico->nombre_grado_academico ?? 'Registrar datos de la persona');

        $templateProcessor->setValue('persona.areaformacion', $incorporacion->persona->formacion[0]->areaFormacion->nombre_area_formacion ?? 'Registrar datos de la persona');

        $templateProcessor->setValue('persona.institucion', $incorporacion->persona->formacion[0]->institucion->nombre_institucion ?? 'Registrar datos de la persona');

        $formaciones = $incorporacion->persona->formacion->first();
        if ($formaciones) {
            $carbonFechaConclusion = Carbon::parse($formaciones->gestion_formacion);
            $year = $carbonFechaConclusion->year;
            $templateProcessor->setValue('persona.gestionFormacion', $year);
        } else {
            $templateProcessor->setValue('persona.gestionFormacion', 'Registrar datos de la persona');
        }

        if (!empty($incorporacion->persona->formacion[0]->gradoAcademico->nombre_grado_academico) && !empty($incorporacion->persona->formacion[0]->areaFormacion->nombre_area_formacion)) {
            $profesion = $incorporacion->persona->formacion[0]->gradoAcademico->nombre_grado_academico . ' en ' . $incorporacion->persona->formacion[0]->areaFormacion->nombre_area_formacion;
            $templateProcessor->setValue('persona.profesion', $profesion);
        } else {
            $templateProcessor->setValue('persona.profesion', 'Registrar datos de la persona');
        }

        if (!empty($incorporacion->persona->profesion_persona)) {
            $templateProcessor->setValue('persona.profesionCambioItem', $incorporacion->persona->profesion_persona);
        } else {
            $templateProcessor->setValue('persona.profesionCambioItem', 'Registrar datos de la persona');
        }

        $respaldo = $incorporacion->obs_evaluacion_incorporacion;
        $valorRespaldo = ($respaldo == 'Cumple') ? 'Si' : 'No';
        $templateProcessor->setValue('persona.respaldo', $valorRespaldo);

        if (isset($incorporacion->puesto_actual) && isset($incorporacion->puesto_actual->item_puesto)) {
            $templateProcessor->setValue('puestoActual.item', $incorporacion->puesto_actual->item_puesto);
        } else {
            $templateProcessor->setValue('puestoActual.item', 'Valor predeterminado o mensaje de error');
        }

        $denominacion_puesto = isset($incorporacion->puesto_actual->denominacion_puesto) ? $incorporacion->puesto_actual->denominacion_puesto : 'Valor predeterminado o mensaje de error';
        $templateProcessor->setValue('puestoActual.denominacionMayuscula', mb_strtoupper($denominacion_puesto, 'UTF-8'));

        if (isset($incorporacion->puesto_actual->departamento->nombre_departamento)) {
            $nombreDepartamento = mb_strtoupper($incorporacion->puesto_actual->departamento->nombre_departamento, 'UTF-8');
            $inicialDepartamento = mb_strtoupper(substr($nombreDepartamento, 0, 1), 'UTF-8');
            if (in_array($inicialDepartamento, ['D'])) {
                $valorDepartamento = 'DEL ' . $nombreDepartamento;
            } elseif (in_array($inicialDepartamento, ['G', 'U'])) {
                $valorDepartamento = 'DE LA ' . $nombreDepartamento;
            } else {
                $valorDepartamento = 'DE ' . $nombreDepartamento;
            }
            $templateProcessor->setValue('puestoActual.departamentoMayuscula', $valorDepartamento);
        } else {
            $templateProcessor->setValue('puestoActual.departamentoMayuscula', 'Valor predeterminado o mensaje de error');
        }

        if (isset($incorporacion->puesto_actual->departamento->gerencia->nombre_gerencia)) {
            $nombreGerencia = mb_strtoupper($incorporacion->puesto_actual->departamento->gerencia->nombre_gerencia, 'UTF-8');
            $inicialGerencia = mb_strtoupper(substr($nombreGerencia, 0, 1), 'UTF-8');
            if (in_array($inicialGerencia, ['P'])) {
                $valorGerencia = 'DE ' . $nombreGerencia;
            } else {
                $valorGerencia = 'DE LA ' . $nombreGerencia;
            }
            $templateProcessor->setValue('puestoActual.gerenciaMayuscula', $valorGerencia);
        } else {
            $templateProcessor->setValue('puestoActual.gerenciaMayuscula', 'Valor predeterminado o mensaje de error');
        }

        $denominacion_puesto = isset($incorporacion->puesto_actual->denominacion_puesto) ? $incorporacion->puesto_actual->denominacion_puesto : 'Valor predeterminado o mensaje de error';
        $templateProcessor->setValue('puestoActual.denominacion', $denominacion_puesto);

        if (isset($incorporacion->puesto_actual->departamento) && isset($incorporacion->puesto_actual->departamento->gerencia)) {
            $templateProcessor->setValue('puestoActual.gerencia', $incorporacion->puesto_actual->departamento->gerencia->nombre_gerencia);
            $templateProcessor->setValue('puestoActual.departamento', $incorporacion->puesto_actual->departamento->nombre_departamento);
        } else {
            $templateProcessor->setValue('puestoActual.gerencia', 'Valor predeterminado o mensaje de error');
            $templateProcessor->setValue('puestoActual.departamento', 'Valor predeterminado o mensaje de error');
        }

        if (isset($incorporacion->puesto_actual->salario_puesto)) {
            $salarioFormateado = number_format($incorporacion->puesto_actual->salario_puesto, 0, '.', ',');
            $templateProcessor->setValue('puestoActual.salario', $salarioFormateado);
        } else {
            $templateProcessor->setValue('puestoActual.salario', 'Valor predeterminado o mensaje de error');
        }

        $templateProcessor->setValue('puestoNuevo.item', $incorporacion->puesto_nuevo->item_puesto);

        $templateProcessor->setValue('puestoNuevo.denominacionMayuscula', mb_strtoupper($incorporacion->puesto_nuevo->denominacion_puesto, 'UTF-8'));

        $nombreDepartamento = mb_strtoupper($incorporacion->puesto_nuevo->departamento->nombre_departamento, 'UTF-8');
        $inicialDepartamento = mb_strtoupper(substr($nombreDepartamento, 0, 1), 'UTF-8');
        if (in_array($inicialDepartamento, ['D'])) {
            $valorDepartamento = 'DEL ' . $nombreDepartamento;
        } elseif (in_array($inicialDepartamento, ['G', 'U'])) {
            $valorDepartamento = 'DE LA ' . $nombreDepartamento;
        } else {
            $valorDepartamento = 'DE ' . $nombreDepartamento;
        }
        $templateProcessor->setValue('puestoNuevo.departamentoMayuscula', $valorDepartamento);

        $nombreGerencia = mb_strtoupper($incorporacion->puesto_nuevo->departamento->gerencia->nombre_gerencia, 'UTF-8');
        $inicialGerencia = mb_strtoupper(substr($nombreGerencia, 0, 1), 'UTF-8');
        if (in_array($inicialGerencia, ['P'])) {
            $valorDepartamento = 'DE ' . $nombreGerencia;
        } else {
            $valorDepartamento = 'DE LA ' . $nombreGerencia;
        }
        $templateProcessor->setValue('puestoNuevo.gerenciaMayuscula', $valorDepartamento);

        $templateProcessor->setValue('puestoNuevo.denominacion', $incorporacion->puesto_nuevo->denominacion_puesto);

        $templateProcessor->setValue('puestoNuevo.gerencia', $incorporacion->puesto_nuevo->departamento->gerencia->nombre_gerencia);

        $templateProcessor->setValue('puestoNuevo.departamento', $incorporacion->puesto_nuevo->departamento->nombre_departamento);

        $salarioFormateado = number_format($incorporacion->puesto_nuevo->salario_puesto / 1000, 3, '.', '');
        $templateProcessor->setValue('puestoNuevo.salario', $salarioFormateado);

        $templateProcessor->setValue('puestoNuevo.salarioLiteral', $incorporacion->puesto_nuevo->salario_literal_puesto);

        if ($incorporacion) {
            $puestoNuevo = $incorporacion->puesto_nuevo;
            if ($puestoNuevo) {
                $requisitosPuestoNuevo = $puestoNuevo->requisitos;
                if ($requisitosPuestoNuevo->isNotEmpty()) {
                    $primerRequisitoPuestoNuevo = $requisitosPuestoNuevo->first();
                    if ($primerRequisitoPuestoNuevo) {
                        $formacionRequerida = $primerRequisitoPuestoNuevo->formacion_requisito;
                        $expProfesionalSegunCargo = $primerRequisitoPuestoNuevo->exp_cargo_requisito;
                        $expRelacionadoAlArea = $primerRequisitoPuestoNuevo->exp_area_requisito;
                        $expEnFuncionesDeMando = $primerRequisitoPuestoNuevo->exp_mando_requisito;

                        $templateProcessor->setValue('puestoNuevo.formacion', $formacionRequerida);
                        $templateProcessor->setValue('puestoNuevo.expSegunCargo', $expProfesionalSegunCargo);
                        $templateProcessor->setValue('puestoNuevo.expSegunArea', $expRelacionadoAlArea);
                        $templateProcessor->setValue('puestoNuevo.expEnMando', $expEnFuncionesDeMando);
                    }
                } else {
                    $templateProcessor->setValue('puestoNuevo.formacion', 'Registre requisitos');
                    $templateProcessor->setValue('puestoNuevo.expSegunCargo', 'Registre requisitos');
                    $templateProcessor->setValue('puestoNuevo.expSegunArea', 'Registre requisitos');
                    $templateProcessor->setValue('puestoNuevo.expEnMando', 'Registre requisitos');
                }
            }
        }

        $templateProcessor->setValue('puestoNuevo.cumpleExpSegunCargo', $this->obtenerTextoSegunValor($incorporacion->cumple_exp_profesional_incorporacion));
        $templateProcessor->setValue('puestoNuevo.cumpleExpSegunArea', $this->obtenerTextoSegunValor($incorporacion->cumple_exp_especifica_incorporacion));
        $templateProcessor->setValue('puestoNuevo.cumpleExpEnMando', $this->obtenerTextoSegunValor($incorporacion->cumple_exp_mando_incorporacion));
        $templateProcessor->setValue('puestoNuevo.cumpleFormacion', $this->obtenerTextoSegunValorDeFormacion($incorporacion->cumple_formacion_incorporacion));

        $nombreDepartamento = $incorporacion->puesto_nuevo->departamento->nombre_departamento;
        $inicialDepartamento = substr($nombreDepartamento, 0, 1);
        if (in_array($inicialDepartamento, ['D'])) {
            $valorDepartamento = 'del ' . $nombreDepartamento;
        } elseif (in_array($inicialDepartamento, ['G', 'U'])) {
            $valorDepartamento = 'de la ' . $nombreDepartamento;
        } else {
            $valorDepartamento = 'de ' . $nombreDepartamento;
        }
        $templateProcessor->setValue('puestoNuevo.departamentoRef', $valorDepartamento);

        $nombreGerencia = $incorporacion->puesto_nuevo->departamento->gerencia->nombre_gerencia;
        $inicialGerencia = substr($nombreGerencia, 0, 1);
        if (in_array($inicialGerencia, ['P'])) {
            $valorDepartamento = 'de ' . $nombreGerencia;
        } else {
            $valorDepartamento = 'de la ' . $nombreGerencia;
        }

        $templateProcessor->setValue('puestoNuevo.gerenciaRef', $valorDepartamento);

        $carbonFechaIncorporacion = Carbon::parse($incorporacion->fch_incorporacion);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaIncorporacion->locale('es_UY');
        $fechaIncorporacionFormateada = $carbonFechaIncorporacion->isoFormat('LL');
        $templateProcessor->setValue('incorporacion.fechaIncorporacion', $fechaIncorporacionFormateada);
        $templateProcessor->setValue('persona.ci', $incorporacion->persona->ci_persona);

        $templateProcessor->setValue('persona.exp', $incorporacion->persona->exp_persona);

        $nombreGerencia = $incorporacion->puesto_nuevo->departamento->gerencia->nombre_gerencia;
        $valorGerencia = '';

        if ($nombreGerencia == 'Gerencia Distrital La Paz I') {
            $valorGerencia = 'GDLPZ I';
        } elseif ($nombreGerencia == 'Gerencia Distrital La Paz II') {
            $valorGerencia = 'GDLPZ II';
        } elseif ($nombreGerencia == 'Gerencia GRACO La Paz') {
            $valorGerencia = 'GGLPZ';
        } elseif ($nombreGerencia == 'Gerencia Distrital Cochabamba') {
            $valorGerencia = 'GDCBBA';
        } elseif ($nombreGerencia == 'Gerencia GRACO Cochabamba') {
            $valorGerencia = 'GGCBBA';
        } elseif ($nombreGerencia == 'Gerencia Distrital Quillacollo') {
            $valorGerencia = 'GDQLLO';
        } elseif ($nombreGerencia == 'Gerencia Distrital Santa Cruz I') {
            $valorGerencia = 'GDSCZ I';
        } elseif ($nombreGerencia == 'Gerencia Distrital Santa Cruz II') {
            $valorGerencia = 'GDSCZ II';
        } elseif ($nombreGerencia == 'Gerencia GRACO Santa Cruz') {
            $valorGerencia = 'GGSCZ';
        } elseif ($nombreGerencia == 'Gerencia Distrital Montero') {
            $valorGerencia = 'GDMTR';
        } elseif ($nombreGerencia == 'Gerencia Distrital Chuquisaca') {
            $valorGerencia = 'GDCH';
        } elseif ($nombreGerencia == 'Gerencia Distrital Tarija') {
            $valorGerencia = 'GDTJ';
        } elseif ($nombreGerencia == 'Gerencia Distrital Yacuiba') {
            $valorGerencia = 'GDYA';
        } elseif ($nombreGerencia == 'Gerencia Distrital Oruro') {
            $valorGerencia = 'GDOR';
        } elseif ($nombreGerencia == 'Gerencia Distrital Potosí') {
            $valorGerencia = 'GDPT';
        } elseif ($nombreGerencia == 'Gerencia Distrital Beni') {
            $valorGerencia = 'GDBN';
        } elseif ($nombreGerencia == 'Gerencia Distrital Pando') {
            $valorGerencia = 'GDPN';
        } else {
            $valorGerencia = 'GG';
        }
        $templateProcessor->setValue('incorporacion.gerenciaAbreviatura', $valorGerencia);

        $nombreGerencia = $incorporacion->puesto_nuevo->departamento->gerencia->nombre_gerencia;
        $valorDepartamento = '';

        if (
            $nombreGerencia === 'Gerencia Distrital La Paz I' ||
            $nombreGerencia === 'Gerencia GRACO La Paz' ||
            $nombreGerencia === 'Gerencia Distrital Cochabamba' ||
            $nombreGerencia === 'Gerencia Distrital Santa Cruz I' ||
            $nombreGerencia === 'Gerencia GRACO Santa Cruz'
        ) {
            $valorDepartamento = 'DARH';
        } elseif (
            $nombreGerencia === 'Gerencia Distrital La Paz II' ||
            $nombreGerencia === 'Gerencia Distrital Quillacollo' ||
            $nombreGerencia === 'Gerencia Distrital Santa Cruz II' ||
            $nombreGerencia === 'Gerencia Distrital Montero' ||
            $nombreGerencia === 'Gerencia Distrital Chuquisaca' ||
            $nombreGerencia === 'Gerencia Distrital Tarija' ||
            $nombreGerencia === 'Gerencia Distrital Yacuiba' ||
            $nombreGerencia === 'Gerencia Distrital Oruro' ||
            $nombreGerencia === 'Gerencia Distrital Potosí' ||
            $nombreGerencia === 'Gerencia Distrital Beni' ||
            $nombreGerencia === 'Gerencia Distrital Pando'
        ) {
            $valorDepartamento = 'ARH';
        } else {
            $valorDepartamento = 'GRH';
        }
        $templateProcessor->setValue('incorporacion.departamentoAbreviatura', $valorDepartamento);

        $nombreGerencia = $incorporacion->puesto_nuevo->departamento->gerencia->nombre_gerencia;
        switch ($nombreGerencia) {
            case 'El Alto':
                $ubicacion = 'El Alto';
                break;
            case 'Cochabamba':
            case 'GRACO Cochabamba':
                $ubicacion = 'Cochabamba';
                break;
            case 'Quillacollo':
                $ubicacion = 'Quillacollo';
                break;
            case 'Santa Cruz I':
            case 'Santa Cruz II':
            case 'GRACO Santa Cruz':
                $ubicacion = 'Santa Cruz';
                break;
            case 'Montero':
                $ubicacion = 'Montero';
                break;
            case 'Chuquisaca':
                $ubicacion = 'Chuquisaca';
                break;
            case 'Tarija':
                $ubicacion = 'Tarija';
                break;
            case 'Yacuiba':
                $ubicacion = 'Yacuiba';
                break;
            case 'Oruro':
                $ubicacion = 'Oruro';
                break;
            case 'Potosí':
                $ubicacion = 'Potosí';
                break;
            case 'Beni':
                $ubicacion = 'Beni';
                break;
            case 'Pando':
                $ubicacion = 'Pando';
                break;
            default:
                $ubicacion = 'La Paz';
                break;
        }
        $templateProcessor->setValue('puestoNuevo.gerenciaUbicacion', $ubicacion);
    }

    public function generarInfMinuta($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);

        if (!isset($incorporacion)) {
            return response('', 404);
        }

        $disk = Storage::disk('form_templates');

        if (isset($incorporacion->puesto_actual) && isset($incorporacion->puesto_nuevo)) {
            if (preg_match('/^(Gerente|Secretaria|Jefe de Unidad|Servicios Generales Ejecutivo|Responsable Staff)/', $incorporacion->puesto_nuevo->denominacion_puesto)) {
                $pathTemplate = $disk->path('/libreNombramiento/cambioItem/infMinutaCambioItem.docx');
            } else {
                $pathTemplate = $disk->path('/cambioItem/infMinutaCambioItem.docx');
            }
        } elseif (isset($incorporacion->puesto_nuevo)) {
            if (preg_match('/^(Gerente|Secretaria|Jefe de Unidad|Servicios Generales Ejecutivo|Responsable Staff)/', $incorporacion->puesto_nuevo->denominacion_puesto)) {
                $pathTemplate = $disk->path('/libreNombramiento/incorporacion/infMinuta.docx');
            } else {
                $pathTemplate = $disk->path('/incorporacion/infMinuta.docx');
            }
        }

        $templateProcessor = new TemplateProcessor($pathTemplate);

        $this->valoresComunesByIncorporacion($templateProcessor, $incorporacion);

        $nombreCompleto = $incorporacion->persona->nombre_persona . ' ' . $incorporacion->persona->primer_apellido_persona . ' ' . $incorporacion->persona->segundo_apellido_persona;
        $sexo = $incorporacion->persona->genero_persona;
        if ($sexo === 'F') {
            $templateProcessor->setValue('persona.referenciaMayusculaLibreNombramiento', 'SERVIDORA PÚBLICA DE LIBRE NOMBRAMIENTO DE LA SEÑORA ' . mb_strtoupper($nombreCompleto, 'UTF-8'));
            $templateProcessor->setValue('persona.referenciaMayusculaIncorporacion', 'SERVIDORA PÚBLICA INTERINA DE LA SEÑORA ' . mb_strtoupper($nombreCompleto, 'UTF-8'));
            $templateProcessor->setValue('persona.referenciaIncorporacion', 'servidora pública interina de la señora ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referenciaLibreNombramiento', 'servidora pública de libre nombramiento de la señora ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referencia', 'de la señora ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referenciaAlPrincipio', 'La señora ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referenciaIncorporacion', 'de la señora ' . $nombreCompleto . ' como servidora pública interina');
            $templateProcessor->setValue('persona.referenciaLibreNombramiento', 'de la señora ' . $nombreCompleto . ' como servidora pública de libre nombramiento');
            $templateProcessor->setValue('persona.referenciaMayusculaCambioItem', 'DE LA SERVIDORA PÚBLICA INTERINA ' . mb_strtoupper($nombreCompleto, 'UTF-8'));
            $templateProcessor->setValue('persona.referenciaCambioItem', 'de la servidora pública interina ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referenciaAlPrincipioCambioItem', 'La servidora pública interina ' . $nombreCompleto);
        } else {
            $templateProcessor->setValue('persona.referenciaMayusculaLibreNombramiento', 'SERVIDOR PÚBLICO DE LIBRE NOMBRAMIENTO DEL SEÑOR ' . mb_strtoupper($nombreCompleto, 'UTF-8'));
            $templateProcessor->setValue('persona.referenciaMayusculaIncorporacion', 'SERVIDOR PÚBLICO INTERINO DEL SEÑOR ' . mb_strtoupper($nombreCompleto, 'UTF-8'));
            $templateProcessor->setValue('persona.referenciaIncorporacion', 'servidor público interino del señor ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referenciaLibreNombramiento', 'servidor público de libre nombramiento del señor ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referencia', 'del señor ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referenciaAlPrincipio', 'El señor ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referenciaIncorporacion', 'del señor ' . $nombreCompleto . ' como servidor público interino');
            $templateProcessor->setValue('persona.referenciaLibreNombramiento', 'del señor ' . $nombreCompleto . ' como servidor público de libre nombramiento');
            $templateProcessor->setValue('persona.referenciaMayusculaCambioItem', 'DEL SERVIDOR PÚBLICO INTERINO ' . mb_strtoupper($nombreCompleto, 'UTF-8'));
            $templateProcessor->setValue('persona.referenciaCambioItem', 'del servidor público interino ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referenciaAlPrincipioCambioItem', 'El servidor público interino ' . $nombreCompleto);
        }

        $templateProcessor->setValue('incorporacion.numeroTramite', $incorporacion->n_tramite_incorporacion);

        if (isset($incorporacion->puesto_actual)) {
            $fileName = 'INF MINUTA CAMBIO DE ITEM ' . strtoupper($nombreCompleto) . ' ' . $incorporacion->descargas;
        } else {
            $fileName = 'INF MINUTA ' . strtoupper($nombreCompleto) . ' ' . $incorporacion->descargas;
        }

        $incorporacion->descargas++;

        $savedPath = $disk->path('generados/') . $fileName . '.docx';

        $templateProcessor->saveAs($savedPath);

        return response()->download($savedPath)->deleteFileAfterSend(true);
    }

    public function generarInfNota($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);

        if (!isset($incorporacion)) {
            return response('', 404);
        }

        $disk = Storage::disk('form_templates');

        if (isset($incorporacion->puesto_actual) && isset($incorporacion->puesto_nuevo)) {
            if (preg_match('/^(Gerente|Secretaria|Jefe de Unidad|Servicios Generales Ejecutivo|Responsable Staff)/', $incorporacion->puesto_nuevo->denominacion_puesto)) {
                $pathTemplate = $disk->path('/libreNombramiento/cambioItem/infNotaCambioItem.docx');
            } else {
                $pathTemplate = $disk->path('/cambioItem/infNotaCambioItem.docx');
            }
        } elseif (isset($incorporacion->puesto_nuevo)) {
            if (preg_match('/^(Gerente|Secretaria|Jefe de Unidad|Servicios Generales Ejecutivo|Responsable Staff)/', $incorporacion->puesto_nuevo->denominacion_puesto)) {
                $pathTemplate = $disk->path('/libreNombramiento/incorporacion/infNota.docx');
            } else {
                $pathTemplate = $disk->path('/incorporacion/infNota.docx');
            }
        }

        $templateProcessor = new TemplateProcessor($pathTemplate);

        $this->valoresComunesByIncorporacion($templateProcessor, $incorporacion);

        $nombreCompleto = $incorporacion->persona->nombre_persona . ' ' . $incorporacion->persona->primer_apellido_persona . ' ' . $incorporacion->persona->segundo_apellido_persona;
        $sexo = $incorporacion->persona->genero_persona;
        if ($sexo === 'F') {
            $templateProcessor->setValue('persona.referenciaMayusculaLibreNombramiento', 'SERVIDORA PÚBLICA DE LIBRE NOMBRAMIENTO DE LA SEÑORA ' . mb_strtoupper($nombreCompleto, 'UTF-8'));
            $templateProcessor->setValue('persona.referenciaMayusculaIncorporacion', 'SERVIDORA PÚBLICA INTERINA DE LA SEÑORA ' . mb_strtoupper($nombreCompleto, 'UTF-8'));
            $templateProcessor->setValue('persona.referenciaIncorporacion', 'servidora pública interina de la señora ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referenciaLibreNombramiento', 'servidora pública de libre nombramiento de la señora ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referencia', 'de la señora ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referenciaAlPrincipio', 'La señora ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referenciaIncorporacion', 'de la señora ' . $nombreCompleto . ' como servidora pública interina');
            $templateProcessor->setValue('persona.referenciaLibreNombramiento', 'de la señora ' . $nombreCompleto . ' como servidora pública de libre nombramiento');
            $templateProcessor->setValue('persona.referenciaMayusculaCambioItem', 'DE LA SERVIDORA PÚBLICA INTERINA ' . mb_strtoupper($nombreCompleto, 'UTF-8'));
            $templateProcessor->setValue('persona.referenciaCambioItem', 'de la servidora pública interina ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referenciaAlPrincipioCambioItem', 'La servidora pública interina ' . $nombreCompleto);
        } else {
            $templateProcessor->setValue('persona.referenciaMayusculaLibreNombramiento', 'SERVIDOR PÚBLICO DE LIBRE NOMBRAMIENTO DEL SEÑOR ' . mb_strtoupper($nombreCompleto, 'UTF-8'));
            $templateProcessor->setValue('persona.referenciaMayusculaIncorporacion', 'SERVIDOR PÚBLICO INTERINO DEL SEÑOR ' . mb_strtoupper($nombreCompleto, 'UTF-8'));
            $templateProcessor->setValue('persona.referenciaIncorporacion', 'servidor público interino del señor ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referenciaLibreNombramiento', 'servidor público de libre nombramiento del señor ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referencia', 'del señor ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referenciaAlPrincipio', 'El señor ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referenciaIncorporacion', 'del señor ' . $nombreCompleto . ' como servidor público interino');
            $templateProcessor->setValue('persona.referenciaLibreNombramiento', 'del señor ' . $nombreCompleto . ' como servidor público de libre nombramiento');
            $templateProcessor->setValue('persona.referenciaMayusculaCambioItem', 'DEL SERVIDOR PÚBLICO INTERINO ' . mb_strtoupper($nombreCompleto, 'UTF-8'));
            $templateProcessor->setValue('persona.referenciaCambioItem', 'del servidor publico interino ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referenciaAlPrincipioCambioItem', 'El servidor publico interino ' . $nombreCompleto);
        }

        $templateProcessor->setValue('incorporacion.numeroTramite', $incorporacion->n_tramite_incorporacion);

        if (isset($incorporacion->puesto_actual)) {
            $fileName = 'INF NOTA CAMBIO DE ITEM ' . strtoupper($nombreCompleto) . ' ' . $incorporacion->descargas;
        } else {
            $fileName = 'INF NOTA ' . strtoupper($nombreCompleto) . ' ' . $incorporacion->descargas;
        }

        $incorporacion->descargas++;

        $savedPath = $disk->path('generados/') . $fileName . '.docx';

        $templateProcessor->saveAs($savedPath);

        return response()->download($savedPath)->deleteFileAfterSend(true);
    }

    public function generarRap($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);

        if (!isset($incorporacion)) {
            return response('', 404);
        }

        $disk = Storage::disk('form_templates');

        if (isset($incorporacion->puesto_actual) && isset($incorporacion->puesto_nuevo)) {
            if (preg_match('/^(Gerente|Secretaria|Jefe de Unidad|Servicios Generales Ejecutivo|Responsable Staff)/', $incorporacion->puesto_nuevo->denominacion_puesto)) {
                $pathTemplate = $disk->path('/libreNombramiento/cambioItem/rapCambioItem.docx');
            } else {
                $pathTemplate = $disk->path('/cambioItem/rapCambioItem.docx');
            }
        } elseif (isset($incorporacion->puesto_nuevo)) {
            if (preg_match('/^(Gerente|Secretaria|Jefe de Unidad|Servicios Generales Ejecutivo|Responsable Staff)/', $incorporacion->puesto_nuevo->denominacion_puesto)) {
                $pathTemplate = $disk->path('/libreNombramiento/incorporacion/rap.docx');
            } else {
                $pathTemplate = $disk->path('/incorporacion/rap.docx');
            }
        }

        $templateProcessor = new TemplateProcessor($pathTemplate);

        $this->valoresComunesByIncorporacion($templateProcessor, $incorporacion);

        $templateProcessor->setValue('incorporacion.citeRap', $incorporacion->cite_rap_incorporacion);

        $templateProcessor->setValue('incorporacion.codigoRap', $incorporacion->codigo_rap_incorporacion);

        $carbonFechaRap = Carbon::parse($incorporacion->fch_rap_incorporacion);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaRap->locale('es_UY');
        $fechaRapFormateada = $carbonFechaRap->isoFormat('LL');
        $templateProcessor->setValue('incorporacion.fechaRap', $fechaRapFormateada);

        $templateProcessor->setValue('codigoRap', $incorporacion->codigo_rap_incorporacion);

        $nombreCompleto = $incorporacion->persona->nombre_persona . ' ' . $incorporacion->persona->primer_apellido_persona . ' ' . $incorporacion->persona->segundo_apellido_persona;
        $sexo = $incorporacion->persona->genero_persona;
        if ($sexo === 'F') {
            $templateProcessor->setValue('persona.refCambioItem', 'de la servidora pública ' . $nombreCompleto);
            $templateProcessor->setValue('persona.reasignada', 'a la servidora pública interina ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referencia', 'de la señora ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referenciaInc', 'a la señora ' . $nombreCompleto);
        } else {
            $templateProcessor->setValue('persona.refCambioItem', 'del servidor publico ' . $nombreCompleto);
            $templateProcessor->setValue('persona.reasignada', 'al servidor publico interino ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referencia', 'del señor ' . $nombreCompleto);
            $templateProcessor->setValue('persona.referenciaInc', 'al señor ' . $nombreCompleto);
        }

        if (isset($incorporacion->puesto_actual)) {
            $fileName = 'RAP CAMBIO DE ITEM ' . strtoupper($nombreCompleto) . ' ' . $incorporacion->descargas;
        } else {
            $fileName = 'RAP ' . strtoupper($nombreCompleto) . ' ' . $incorporacion->descargas;
        }

        $incorporacion->descargas++;

        $savedPath = $disk->path('generados/') . $fileName . '.docx';

        $templateProcessor->saveAs($savedPath);

        return response()->download($savedPath)->deleteFileAfterSend(true);
    }

    public function generarMemorandum($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);

        if (!isset($incorporacion)) {
            return response('', 404);
        }

        $disk = Storage::disk('form_templates');

        if (isset($incorporacion->puesto_actual) && isset($incorporacion->puesto_nuevo)) {
            if (preg_match('/^(Gerente|Secretaria|Jefe de Unidad|Servicios Generales Ejecutivo|Responsable Staff)/', $incorporacion->puesto_nuevo->denominacion_puesto)) {
                $pathTemplate = $disk->path('/libreNombramiento/cambioItem/memorandumCambioItem.docx');
            } else {
                $pathTemplate = $disk->path('/cambioItem/memorandumCambioItem.docx');
            }
        } elseif (isset($incorporacion->puesto_nuevo)) {
            if (preg_match('/^(Gerente|Secretaria|Jefe de Unidad|Servicios Generales Ejecutivo|Responsable Staff)/', $incorporacion->puesto_nuevo->denominacion_puesto)) {
                $pathTemplate = $disk->path('/libreNombramiento/incorporacion/memorandum.docx');
            } else {
                $pathTemplate = $disk->path('/incorporacion/memorandum.docx');
            }
        }

        $templateProcessor = new TemplateProcessor($pathTemplate);

        $this->valoresComunesByIncorporacion($templateProcessor, $incorporacion);

        $templateProcessor->setValue('incorporacion.codigoRap', $incorporacion->codigo_rap_incorporacion);

        $templateProcessor->setValue('incorporacion.citeMemorandum', $incorporacion->cite_memorandum_incorporacion);

        $templateProcessor->setValue('incorporacion.codigoMemorandum', $incorporacion->codigo_memorandum_incorporacion);

        $carbonFechaMemo = Carbon::parse($incorporacion->fch_memorandum_incorporacion);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaMemo->locale('es_UY');
        $fechaMemoFormateada = $carbonFechaMemo->isoFormat('LL');
        $templateProcessor->setValue('incorporacion.fechaMemorandum', $fechaMemoFormateada);

        $primerApellido = $incorporacion->persona->primer_apellido_persona;
        $genero = $incorporacion->persona->genero_persona;

        if ($genero === 'F') {
            $templateProcessor->setValue('persona.para', 'Señora ' . $primerApellido);
            $templateProcessor->setValue('persona.asignada', 'designada');
            $templateProcessor->setValue('persona.designada', 'designada');
            $templateProcessor->setValue('persona.reasignada', 'reasignada');
        } else {
            $templateProcessor->setValue('persona.para', 'Señor ' . $primerApellido);
            $templateProcessor->setValue('persona.asignada', 'designado');
            $templateProcessor->setValue('persona.designada', 'designado');
            $templateProcessor->setValue('persona.reasignada', 'reasignado');
        }

        if (isset($incorporacion->puesto_actual)) {
            $denominacion_puesto = $incorporacion->puesto_actual->denominacion_puesto;
        } else {
            $denominacion_puesto = $incorporacion->puesto_nuevo->denominacion_puesto;
        }
        $denominacion_puestoEnMayusculas = mb_strtoupper($denominacion_puesto, 'UTF-8');
        $templateProcessor->setValue('puestoActual.denominacion', $denominacion_puestoEnMayusculas);

        if (isset($incorporacion->puesto_actual)) {
            $fileName = 'MEM CAMBIO DE ITEM ' . strtoupper($incorporacion->persona->nombre_persona . ' ' . $incorporacion->persona->primer_apellido_persona . ' ' . $incorporacion->persona->segundo_apellido_persona) . ' ' . $incorporacion->descargas;
        } else {
            $fileName = 'MEM ' . strtoupper($incorporacion->persona->nombre_persona . ' ' . $incorporacion->persona->primer_apellido_persona . ' ' . $incorporacion->persona->segundo_apellido_persona) . ' ' . $incorporacion->descargas;
        }

        $incorporacion->descargas++;

        $savedPath = $disk->path('generados/') . $fileName . '.docx';

        $templateProcessor->saveAs($savedPath);

        return response()->download($savedPath)->deleteFileAfterSend(true);
    }

    public function generarActaEntrega($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);

        if (!isset($incorporacion)) {
            return response('', 404);
        }

        $disk = Storage::disk('form_templates');
        if (isset($incorporacion->puesto_actual)) {
            $pathTemplate = $disk->path('actaEntregaCambioItem.docx');
        } else {
            $pathTemplate = $disk->path('actaEntrega.docx');
        }

        $templateProcessor = new TemplateProcessor($pathTemplate);

        $this->valoresComunesByIncorporacion($templateProcessor, $incorporacion);

        if (isset($incorporacion->puesto_actual)) {
            $fileName = 'Acta Entrega Cambio de item ' . $incorporacion->persona->nombre_persona . ' ' . $incorporacion->persona->primer_apellido_persona . ' ' . $incorporacion->persona->segundo_apellido_persona . ' ' . $incorporacion->descargas;
        } else {
            $fileName = 'Acta Entrega ' . $incorporacion->persona->nombre_persona . ' ' . $incorporacion->persona->primer_apellido_persona . ' ' . $incorporacion->persona->segundo_apellido_persona . ' ' . $incorporacion->descargas;
        }

        $incorporacion->descargas++;

        $savedPath = $disk->path('generados/') . $fileName . '.docx';

        $templateProcessor->saveAs($savedPath);

        return response()->download($savedPath)->deleteFileAfterSend(true);
    }

    public function generarActaPosesion($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);

        if (!isset($incorporacion)) {
            return response('', 404);
        }

        $disk = Storage::disk('form_templates');
        $pathTemplate = $disk->path('ActaDePosesionCambioDeItem.docx');

        if (isset($incorporacion->puesto_actual)) {
            $pathTemplate = $disk->path('actaPosesionCambioItem.docx');
        } else {
            $pathTemplate = $disk->path('actaPosesion.docx');
        }

        $templateProcessor = new TemplateProcessor($pathTemplate);

        $this->valoresComunesByIncorporacion($templateProcessor, $incorporacion);

        $carbonFechaIncorporacion = Carbon::parse($incorporacion->fch_incorporacion);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaIncorporacion->locale('es_UY');
        $fechaIncorporacionFormateada = $carbonFechaIncorporacion->isoFormat('LL');
        $nombreDiaIncorporacion = $carbonFechaIncorporacion->isoFormat('dddd');
        $templateProcessor->setValue('incorporacion.nombreDiaIncorporacion', $nombreDiaIncorporacion);

        $templateProcessor->setValue('incorporacion.codigoRap', $incorporacion->codigo_rap_incorporacion);

        $nombre_gerencia = $incorporacion->puesto_nuevo->departamento->gerencia->nombre_gerencia;
        $nombre_gerencia = str_replace("Gerencia", "Gerente", $nombre_gerencia);
        $templateProcessor->setValue('puestoNuevo.gerente', $nombre_gerencia);

        $sexo = $incorporacion->persona->genero_persona;

        if ($sexo === 'F') {
            $templateProcessor->setValue('persona.ciudadano', 'la ciudadana');

            $templateProcessor->setValue('persona.designado', 'designada');

            $templateProcessor->setValue('persona.asignado', 'asignada');
        } else {
            $templateProcessor->setValue('persona.ciudadano', 'el ciudadano');

            $templateProcessor->setValue('persona.designado', 'designado');

            $templateProcessor->setValue('persona.asignado', 'asignada');
        }

        if (isset($incorporacion->puesto_actual)) {
            $fileName = 'Acta de Posesion Cambio de Item ' . $incorporacion->persona->nombre_persona . ' ' . $incorporacion->persona->primer_apellido_persona . ' ' . $incorporacion->persona->segundo_apellido_persona . ' ' . $incorporacion->descargas;
        } else {
            $fileName = 'Acta de Posesion ' . $incorporacion->persona->nombre_persona . ' ' . $incorporacion->persona->primer_apellido_persona . ' ' . $incorporacion->persona->segundo_apellido_persona . ' ' . $incorporacion->descargas;
        }

        $incorporacion->descargas++;

        $savedPath = $disk->path('generados/') . $fileName . '.docx';

        $templateProcessor->saveAs($savedPath);

        return response()->download($savedPath)->deleteFileAfterSend(true);
    }

    public function generarFormR1418($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);

        if (!isset($incorporacion)) {
            return response('', 404);
        }

        $disk = Storage::disk('form_templates');
        $pathTemplate = $disk->path('R-1418-01.xlsx');

        $spreadsheet = IOFactory::load($pathTemplate);

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('D8', $incorporacion->puesto_nuevo->departamento->gerencia->nombre_gerencia);

        $sheet->setCellValue('S8', $incorporacion->puesto_nuevo->departamento->nombre_departamento);

        $sheet->setCellValue('AG8', $incorporacion->puesto_nuevo->item_puesto);

        $sheet->setCellValue('AK8', $incorporacion->puesto_nuevo->denominacion_puesto);

        $sheet->setCellValue('J10', $incorporacion->persona->ci_persona);

        $sheet->setCellValue('S10', $incorporacion->persona->nombre_persona . ' ' . $incorporacion->persona->primer_apellido_persona . ' ' . $incorporacion->persona->segundo_apellido_persona);

        $sheet->setCellValue('AQ10', $incorporacion->fch_incorporacion);

        $fileName = 'R-1418-01 ' . $incorporacion->persona->nombre_persona . ' ' . $incorporacion->persona->primer_apellido_persona . ' ' . $incorporacion->persona->segundo_apellido_persona . ' ' . $incorporacion->descargas;

        $incorporacion->descargas++;

        $savedPath = $disk->path('generados/') . $fileName . '.xlsx';
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($savedPath);

        return response()->download($savedPath)->deleteFileAfterSend(true);
    }

    public function generarFormR1419($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);

        if (!$incorporacion) {
            return response('', 404);
        }

        $imagen_persona = $incorporacion->persona->imagenes->first();

        $disk = Storage::disk('form_templates');
        $pathTemplate = $disk->path('R-1419-01.xlsx');
        $spreadsheet = IOFactory::load($pathTemplate);
        $sheet = $spreadsheet->getActiveSheet();

        if ($imagen_persona) {
            $base64_imagen = $imagen_persona->base64_imagen;
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing();
            $drawing->setName('Imagen');
            $drawing->setDescription('Imagen de Persona');
            $drawing->setImageResource(imagecreatefromstring(base64_decode($base64_imagen)));
            $drawing->setRenderingFunction(\PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::RENDERING_JPEG);
            $drawing->setMimeType(\PhpOffice\PhpSpreadsheet\Worksheet\MemoryDrawing::MIMETYPE_DEFAULT);
            $sheet->mergeCells('D7:E18');
            $drawing->setCoordinates('D7');
            $drawing->setHeight(300);
            $drawing->setWidth(180);
            $drawing->setWorksheet($sheet);
        } else {
            $sheet->setCellValue('D7', 'FOTO');
            $sheet->mergeCells('D7:E18');
        }

        $sheet->setCellValue('H7', $incorporacion->persona->nombre_persona . ' ' . $incorporacion->persona->primer_apellido_persona . ' ' . $incorporacion->persona->segundo_apellido_persona);
        $sheet->setCellValue('H9', $incorporacion->puesto_nuevo->denominacion_puesto);
        $sheet->setCellValue('H11', $incorporacion->puesto_nuevo->departamento->gerencia->nombre_gerencia);
        $sheet->setCellValue('H13', $incorporacion->puesto_nuevo->departamento->nombre_departamento);

        $carbonFechaIncorporacion = Carbon::parse($incorporacion->fch_incorporacion);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaIncorporacion->locale('es_UY');
        $fechaIncorporacionFormateada = $carbonFechaIncorporacion->isoFormat('LL');
        $sheet->setCellValue('H15', $fechaIncorporacionFormateada);

        $fileName = 'R-1419-01 ' . $incorporacion->persona->nombre_persona . ' ' . $incorporacion->persona->primer_apellido_persona . ' ' . $incorporacion->persona->segundo_apellido_persona . ' ' . $incorporacion->descargas;

        $incorporacion->descargas++;

        $savedPath = $disk->path('generados/') . $fileName . '.xlsx';

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($savedPath);

        return response()->download($savedPath)->deleteFileAfterSend(true);
    }

    //otros formularios de incorporacion
    public function generarR0716($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);

        if (!isset($incorporacion)) {
            return response('', 404);
        }

        $disk = Storage::disk('form_templates');
        $pathTemplate = $disk->path('/incorporacion/R-0716-01.docx'); // ruta de plantilla

        $templateProcessor = new TemplateProcessor($pathTemplate);

        $this->valoresComunesByIncorporacion($templateProcessor, $incorporacion);

        $fileName = 'R-0716-01 ' . $incorporacion->persona->nombre_persona . ' ' . $incorporacion->persona->primer_apellido_persona . ' ' . $incorporacion->persona->segundo_apellido_persona . ' ' . $incorporacion->descargas;

        $incorporacion->descargas++;

        $savedPath = $disk->path('generados/') . $fileName . '.docx';

        $templateProcessor->saveAs($savedPath);

        return response()->download($savedPath)->deleteFileAfterSend(true);
    }

    public function generarR0921($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);

        if (!isset($incorporacion)) {
            return response('', 404);
        }

        $disk = Storage::disk('form_templates');
        $pathTemplate = $disk->path('/incorporacion/R-0921-01.docx'); // ruta de plantilla

        $templateProcessor = new TemplateProcessor($pathTemplate);

        $this->valoresComunesByIncorporacion($templateProcessor, $incorporacion);

        $fileName = 'R-0921-01 ' . $incorporacion->persona->nombre_persona . ' ' . $incorporacion->persona->primer_apellido_persona . ' ' . $incorporacion->persona->segundo_apellido_persona . ' ' . $incorporacion->descargas;

        $incorporacion->descargas++;

        $savedPath = $disk->path('generados/') . $fileName . '.docx';

        $templateProcessor->saveAs($savedPath);

        return response()->download($savedPath)->deleteFileAfterSend(true);
    }

    public function generarR0976($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);
        if (!isset($incorporacion)) {
            return response('', 404);
        }

        $disk = Storage::disk('form_templates');
        $pathTemplate = $disk->path('/incorporacion/R-0976-01.docx'); // ruta de plantilla

        $templateProcessor = new TemplateProcessor($pathTemplate);

        $this->valoresComunesByIncorporacion($templateProcessor, $incorporacion);

        $fileName = 'R-0976-01 ' . $incorporacion->persona->nombre_persona . ' ' . $incorporacion->persona->primer_apellido_persona . ' ' . $incorporacion->persona->segundo_apellido_persona . ' ' . $incorporacion->descargas;

        $incorporacion->descargas++;

        $savedPath = $disk->path('generados/') . $fileName . '.docx';

        $templateProcessor->saveAs($savedPath);

        return response()->download($savedPath)->deleteFileAfterSend(true);
    }

    public function generarRSGC0033($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);

        if (!isset($incorporacion)) {
            return response('', 404);
        }

        $disk = Storage::disk('form_templates');
        $pathTemplate = $disk->path('/incorporacion/R-SGC-0033-01.docx'); // ruta de plantilla

        $templateProcessor = new TemplateProcessor($pathTemplate);

        $this->valoresComunesByIncorporacion($templateProcessor, $incorporacion);

        $fileName = 'R-SGC-0033-01 ' . $incorporacion->persona->nombre_persona . ' ' . $incorporacion->persona->primer_apellido_persona . ' ' . $incorporacion->persona->segundo_apellido_persona . ' ' . $incorporacion->descargas;

        $incorporacion->descargas++;

        $savedPath = $disk->path('generados/') . $fileName . '.docx';

        $templateProcessor->saveAs($savedPath);

        return response()->download($savedPath)->deleteFileAfterSend(true);
    }

    //reportes en excel de incorporaciones 
    public function genReportEvaluacion(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'nullable|string',
            'fechaInicio' => 'nullable|date',
            'fechaFin' => 'nullable|date',
        ]);

        $name = $request->input('name');
        $fechaInicio = $validatedData['fechaInicio'];
        $fechaFin = $validatedData['fechaFin'];

        $query = Incorporacion::with([
            'persona',
            'persona.funcionario' => function ($query) {
                $query->orderBy('fch_inicio_puesto_funcionario', 'desc');
            },
            'puesto_nuevo:id_puesto,item_puesto,denominacion_puesto,salario_puesto,departamento_id',
            'puesto_nuevo.departamento:id_departamento,nombre_departamento,gerencia_id',
            'puesto_nuevo.departamento.gerencia:id_gerencia,nombre_gerencia',
            'createdBy',
        ]);

        if ($name) {
            $query->whereHas('createdBy', function ($query) use ($name) {
                $query->where('name', $name);
            });
        }

        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('created_at', [$fechaInicio, $fechaFin]);
        }

        $incorporaciones = $query->get();

        $filename = $name ? "Reporte de Evaluacion de {$name}.xlsx" : "Reporte de Evaluacion.xlsx";
        return Excel::download(new ReportEvaluacionExport($incorporaciones), $filename);
    }

    public function genReportTrimestral(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'nullable|string',
            'fechaInicio' => 'nullable|date',
            'fechaFin' => 'nullable|date',
        ]);

        $name = $validatedData['name'] ?? null;
        $fechaInicio = $validatedData['fechaInicio'] ?? null;
        $fechaFin = $validatedData['fechaFin'] ?? null;

        $query = Incorporacion::with([
            'persona',
            'persona.funcionario' => function ($query) {
                $query->orderBy('fch_inicio_puesto_funcionario', 'desc');
            },
            'puesto_nuevo:id_puesto,item_puesto,denominacion_puesto,salario_puesto,departamento_id',
            'puesto_nuevo.departamento:id_departamento,nombre_departamento,gerencia_id',
            'puesto_nuevo.departamento.gerencia:id_gerencia,nombre_gerencia',
            'createdBy',
        ]);

        if ($name) {
            $query->whereHas('createdBy', function ($query) use ($name) {
                $query->where('name', $name);
            });
        }

        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('created_at', [$fechaInicio, $fechaFin]);
        }

        $incorporaciones = $query->get();

        $filename = $name ? "Reporte Trimestral de {$name}.xlsx" : "Reporte Trimestral.xlsx";

        return Excel::download(new ReportTrimestralExport($incorporaciones), $filename);
    }

    public function byCiPersonaFormIncorporacion($ciPersona)
    {
        $persona = Persona::where('ci_persona', $ciPersona)->first();

        if (!$persona) {
            return response()->json(['message' => 'No se encontró ninguna persona con el CI proporcionado.'], 404);
        }

        $currentYear = now()->year;

        $incorporacion = Incorporacion::where('persona_id', $persona->id_persona)
            ->whereYear('created_at', $currentYear)
            ->first();

        if (!$incorporacion) {
            return response()->json(['message' => 'No se encontró ninguna incorporación para la persona con el CI proporcionado.'], 404);
        }

        if (!is_null($incorporacion->puesto_actual_id) && !is_null($incorporacion->puesto_nuevo_id)) {
            return response()->json(['message' => 'La persona con el CI proporcionado, está registrado para un Cambio de Ítem.'], 404);
        }

        $messages = [];

        $fieldsEvaluationAvailable = $incorporacion->persona_id &&
            $incorporacion->puesto_actual_id === null &&
            $incorporacion->puesto_nuevo_id &&
            $incorporacion->obs_evaluacion_incorporacion;

        $fieldsNoteOrMinuteAvailable = $incorporacion->fch_incorporacion &&
            $incorporacion->hp_incorporacion &&
            $incorporacion->cite_informe_incorporacion &&
            $incorporacion->fch_informe_incorporacion &&
            $incorporacion->cumple_exp_profesional_incorporacion &&
            $incorporacion->cumple_exp_especifica_incorporacion &&
            $incorporacion->cumple_exp_mando_incorporacion &&
            $incorporacion->cumple_formacion_incorporacion &&
            $incorporacion->cite_nota_minuta_incorporacion &&
            $incorporacion->fch_nota_minuta_incorporacion;

        $fieldsRapAvailable = $incorporacion->fch_incorporacion &&
            $incorporacion->hp_incorporacion &&
            $incorporacion->cite_rap_incorporacion &&
            $incorporacion->codigo_rap_incorporacion &&
            $incorporacion->fch_rap_incorporacion;

        $fieldsMemoAvailable = $incorporacion->fch_incorporacion &&
            $incorporacion->hp_incorporacion &&
            $incorporacion->cite_memorandum_incorporacion &&
            $incorporacion->codigo_memorandum_incorporacion &&
            $incorporacion->fch_memorandum_incorporacion;

        if ($fieldsEvaluationAvailable) {
            $messages[] = 'Los formularios de evaluación ya están disponibles.';
        }

        if ($fieldsNoteOrMinuteAvailable) {
            $messages[] = 'Inf. con Nota o Minuta ya están disponibles.';
        }

        if ($fieldsRapAvailable) {
            $messages[] = 'RAP ya están disponibles.';
        }

        if ($fieldsMemoAvailable) {
            $messages[] = 'Memorándum ya están disponibles.';
        }

        if (empty($messages)) {
            $messages[] = 'No hay formularios disponibles.';
        }

        return response()->json([
            'message' => implode(' ', $messages),
            'idIncorporacion' => $incorporacion->id_incorporacion,
            'puestoActualId' => $incorporacion->puesto_actual_id,
            'puestoNuevoId' => $incorporacion->puesto_nuevo_id,
        ], 200);
    }

    public function byCiPersonaFormCambioItem($ciPersona)
    {
        $persona = Persona::where('ci_persona', $ciPersona)->first();

        if (!$persona) {
            return response()->json(['message' => 'No se encontró ninguna persona con el CI proporcionado.'], 404);
        }

        $currentYear = now()->year;

        $incorporacion = Incorporacion::where('persona_id', $persona->id_persona)
            ->whereYear('created_at', $currentYear)
            ->first();

        if (!$incorporacion) {
            return response()->json(['message' => 'No se encontró ninguna incorporación para la persona con el CI proporcionado.'], 404);
        }

        if (is_null($incorporacion->puesto_actual_id) && !is_null($incorporacion->puesto_nuevo_id)) {
            return response()->json(['message' => 'La persona con el CI proporcionado, está registrado para una Incorporación.'], 404);
        }

        $messages = [];

        $fieldsEvaluationAvailable = $incorporacion->persona_id &&
            $incorporacion->puesto_actual_id &&
            $incorporacion->puesto_nuevo_id &&
            $incorporacion->obs_evaluacion_incorporacion;

        $fieldsNoteOrMinuteAvailable = $incorporacion->fch_incorporacion &&
            $incorporacion->hp_incorporacion &&
            $incorporacion->cite_informe_incorporacion &&
            $incorporacion->fch_informe_incorporacion &&
            $incorporacion->cumple_exp_profesional_incorporacion &&
            $incorporacion->cumple_exp_especifica_incorporacion &&
            $incorporacion->cumple_exp_mando_incorporacion &&
            $incorporacion->cumple_formacion_incorporacion &&
            $incorporacion->cite_nota_minuta_incorporacion &&
            $incorporacion->fch_nota_minuta_incorporacion;

        $fieldsRapAvailable = $incorporacion->fch_incorporacion &&
            $incorporacion->hp_incorporacion &&
            $incorporacion->cite_rap_incorporacion &&
            $incorporacion->codigo_rap_incorporacion &&
            $incorporacion->fch_rap_incorporacion;

        $fieldsMemoAvailable = $incorporacion->fch_incorporacion &&
            $incorporacion->hp_incorporacion &&
            $incorporacion->cite_memorandum_incorporacion &&
            $incorporacion->codigo_memorandum_incorporacion &&
            $incorporacion->fch_memorandum_incorporacion;

        if ($fieldsEvaluationAvailable) {
            $messages[] = 'Los formularios de evaluación ya están disponibles.';
        }

        if ($fieldsNoteOrMinuteAvailable) {
            $messages[] = 'Inf. con Nota o Minuta ya están disponibles.';
        }

        if ($fieldsRapAvailable) {
            $messages[] = 'RAP ya están disponibles.';
        }

        if ($fieldsMemoAvailable) {
            $messages[] = 'Memorándum ya están disponibles.';
        }

        if (empty($messages)) {
            $messages[] = 'No hay formularios disponibles.';
        }

        return response()->json([
            'message' => implode(' ', $messages),
            'idIncorporacion' => $incorporacion->id_incorporacion,
            'puestoActualId' => $incorporacion->puesto_actual_id,
            'puestoNuevoId' => $incorporacion->puesto_nuevo_id,
        ], 200);
    }

    public function downloadEvalForm($fileName)
    {
        $disk = Storage::disk('form_templates');
        return response()->download($disk->path('generados/') . $fileName)->deleteFileAfterSend(true);
    }

    public function obtenerTextoSegunValor($valor)
    {
        switch ($valor) {
            case 1:
                return 'Si';
            case 2:
                return 'No';
            case 3:
                return 'No corresponde';
            default:
                return 'Valor no reconocido';
        }
    }

    public function obtenerTextoSegunValorDeFormacion($valor)
    {
        switch ($valor) {
            case 1:
                return 'Cumple';
            case 2:
                return 'No Cumple';
            default:
                return 'Valor no reconocido';
        }
    }

    //detalle de las incorporaciones
    public function getIncorporacionDetalle()
    {
        $gestion = Carbon::now()->year;

        $cantidadIncorporacionesCreadas = Incorporacion::whereYear('created_at', $gestion)
            ->where('estado_incorporacion', 2)
            ->count();

        return [
            'cantidad_incorporaciones_creadas' => $cantidadIncorporacionesCreadas
        ];
    }
}

<?php

namespace App\Http\Controllers;

use App\Exports\InterinatosExport;
use App\Imports\InterinatoDataImport;
use App\Models\Interinato;
use App\Models\Persona;
use App\Models\Puesto;
use Dotenv\Exception\InvalidFileException as ExceptionInvalidFileException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\TemplateProcessor;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\RowCellIterator;

class InterinatoController extends Controller
{
    public function crearInterinato(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'puestoNuevoId' => 'required|integer',
                'puestoActualId' => 'required|integer',
                'personaId' => 'required|integer',
                'fchInicioInterinato' => 'required|date',
                'fchFinInterinato' => 'required|date',

                'citeInformeInstruccionInterinato' => 'nullable|string',
                'fchInformeInstruccionInterinato' => 'nullable|date',
                'proveidoInterinato' => 'nullable|string',
                'numTramiteHpInterinato' => 'nullable|string',
                'citeInformeInterinato' => 'nullable|string',
                'fchCiteInformeInterinato' => 'nullable|date',
                'numFojasInformeInterinato' => 'nullable|integer',
                'tipoSolicitudInforme' => 'nullable|integer',

                'citeRapInterinato' => 'nullable|string',
                'codigoRapInterinato' => 'nullable|string',
                'numFojasRapInterinato' => 'nullable|integer',

                'citeMemInterinato' => 'nullable|string',
                'codigoMemInterinato' => 'nullable|string',
                'codigoFileInterinato' => 'nullable|string',
                'fchMemorandumRapInterinato' => 'nullable|date',

                'createdInterinato' => 'nullable|integer',
            ]);

            $interinato = Interinato::create([
                'puesto_nuevo_id' => $validatedData['puestoNuevoId'],
                'puesto_actual_id' => $validatedData['puestoActualId'],
                'persona_id' => $validatedData['personaId'],
                'fch_inicio_interinato' => $validatedData['fchInicioInterinato'],
                'fch_fin_interinato' => $validatedData['fchFinInterinato'],

                'cite_informe_instruccion_interinato' => $validatedData['citeInformeInstruccionInterinato'],
                'fch_informe_instruccion_interinato' => $validatedData['fchInformeInstruccionInterinato'],
                'proveido_interinato' => $validatedData['proveidoInterinato'],
                'num_tramite_hp_interinato' => $validatedData['numTramiteHpInterinato'],
                'cite_informe_interinato' => $validatedData['citeInformeInterinato'],
                'fch_cite_informe_interinato' => $validatedData['fchCiteInformeInterinato'],
                'num_fojas_informe_interinato' => $validatedData['numFojasInformeInterinato'],
                'tipo_solicitud_informe' => $validatedData['tipoSolicitudInforme'],

                'cite_rap_interinato' => $validatedData['citeRapInterinato'],
                'codigo_rap_interinato' => $validatedData['codigoRapInterinato'],
                'num_fojas_rap_interinato' => $validatedData['numFojasRapInterinato'],

                'cite_mem_interinato' => $validatedData['citeMemInterinato'],
                'codigo_mem_interinato' => $validatedData['codigoMemInterinato'],
                'codigo_file_interinato' => $validatedData['codigoFileInterinato'],
                'fch_memorandum_rap_interinato' => $validatedData['fchMemorandumRapInterinato'],

                'created_interinato' => $validatedData['createdInterinato'],
                'estado_interinato' => 1,
            ]);

            return response()->json(['message' => 'Interinato creado correctamente', 'data' => $interinato], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear el interinato: ' . $e->getMessage()], 500);
        }
    }

    public function listarInterinatos(Request $request)
    {
        $limit = $request->input('limit');
        $page = $request->input('page');

        $persona = $request->input('query.persona');
        $puesto = $request->input('query.puesto');
        $estadoInterinato = $request->input('query.estadoInterinato');
        $fechaInicioInterinato = $request->input('query.fechaInicioInterinato');
        $fechaFinInterinato = $request->input('query.fechaFinInterinato');

        $today = now()->toDateString();

        Interinato::where('estado_interinato', '!=', 4)
            ->where('fch_fin_interinato', '<', $today)
            ->update(['estado_interinato' => 2]);

        $query = Interinato::with([
            'personaActual',
            'puestoNuevo:id_puesto,item_puesto,denominacion_puesto,departamento_id',
            'puestoNuevo.departamento:id_departamento,nombre_departamento,gerencia_id',
            'puestoNuevo.departamento.gerencia:id_gerencia,nombre_gerencia',
            'puestoActual:id_puesto,item_puesto,denominacion_puesto,departamento_id',
            'puestoActual.departamento:id_departamento,nombre_departamento,gerencia_id',
            'puestoActual.departamento.gerencia:id_gerencia,nombre_gerencia',
            'createdBy',
            'modifiedBy'
        ])->orderBy('id_interinato', 'desc')->where('estado_interinato', '!=', 4);

        if ($persona) {
            $query->where(function ($q) use ($persona) {
                $q->whereHas('personaActual', function ($q) use ($persona) {
                    $q->whereRaw("CONCAT(nombre_persona, ' ', primer_apellido_persona, ' ', segundo_apellido_persona) LIKE ?", ['%' . $persona . '%']);
                })
                    ->orWhereHas('personaActual', function ($q) use ($persona) {
                        $q->where('ci_persona', 'LIKE', '%' . $persona . '%');
                    });
            });
        }

        if ($puesto) {
            $query->where(function ($q) use ($puesto) {
                $q->whereHas('puestoNuevo', function ($query) use ($puesto) {
                    $query->where('denominacion_puesto', $puesto)
                        ->orWhere('item_puesto', $puesto);
                });
            });
        }

        if ($estadoInterinato !== null) {
            $query->where('estado_interinato', '=', $estadoInterinato);
        }

        if ($fechaInicioInterinato && $fechaFinInterinato) {
            $query->whereBetween('fch_inicio_interinato', [$fechaInicioInterinato, $fechaFinInterinato]);
        } elseif ($fechaInicioInterinato) {
            $query->whereDate('fch_inicio_interinato', '>=', $fechaInicioInterinato);
        } elseif ($fechaFinInterinato) {
            $query->whereDate('fch_fin_interinato', '<=', $fechaFinInterinato);
        }

        $interinatos = $query->paginate($limit, ['*'], 'page', $page);
        return $this->sendPaginated($interinatos);
    }

    private function valoresComunesByInterinato(TemplateProcessor $templateProcessor, Interinato $interinato)
    {
        $templateProcessor->setValue('interinato.citeRap', $interinato->cite_rap_interinato);
        $templateProcessor->setValue('interinato.codigoRap', $interinato->codigo_rap_interinato);
        $templateProcessor->setValue('interinato.numFojasRap', $interinato->num_fojas_rap_interinato);
        $numFojas = $interinato->num_fojas_rap_interinato;
        $numerosEnLetras = [
            0 => 'cero',
            1 => 'uno',
            2 => 'dos',
            3 => 'tres',
            4 => 'cuatro',
            5 => 'cinco',
            6 => 'seis',
            7 => 'siete',
            8 => 'ocho',
            9 => 'nueve',
            10 => 'diez',
            11 => 'once',
            12 => 'doce',
            13 => 'trece',
            14 => 'catorce',
            15 => 'quince',
            16 => 'dieciséis',
            17 => 'diecisiete',
            18 => 'dieciocho',
            19 => 'diecinueve',
            20 => 'veinte',
            30 => 'treinta',
            40 => 'cuarenta',
            50 => 'cincuenta',
            60 => 'sesenta',
            70 => 'setenta',
            80 => 'ochenta',
            90 => 'noventa',
            100 => 'cien',
            200 => 'doscientos',
            300 => 'trescientos',
            400 => 'cuatrocientos',
            500 => 'quinientos',
            600 => 'seiscientos',
            700 => 'setecientos',
            800 => 'ochocientos',
            900 => 'novecientos'
        ];

        if ($numFojas < 0 || $numFojas > 999) {
            $numFojasEnLetras = 'Número fuera de rango';
        } else {
            $numFojasEnLetras = '';

            if ($numFojas >= 100) {
                $centenas = floor($numFojas / 100) * 100;
                $numFojasEnLetras .= $numerosEnLetras[$centenas];
                $numFojas -= $centenas;
                if ($numFojas > 0) {
                    $numFojasEnLetras .= ' ';
                }
            }

            if ($numFojas >= 20) {
                $decenas = floor($numFojas / 10) * 10;
                $numFojasEnLetras .= $numerosEnLetras[$decenas];
                $numFojas -= $decenas;
                if ($numFojas > 0) {
                    $numFojasEnLetras .= ' y ';
                }
            }

            if ($numFojas > 0) {
                $numFojasEnLetras .= $numerosEnLetras[$numFojas];
            }
        }
        $templateProcessor->setValue('interinato.numFojasLetrasRap', $numFojasEnLetras);

        $templateProcessor->setValue('interinato.citeMem', $interinato->cite_mem_interinato);
        $templateProcessor->setValue('interinato.codigoMem', $interinato->codigo_mem_interinato);

        $carbonFechaRapMem = Carbon::parse($interinato->fch_memorandum_rap_interinato);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaRapMem->locale('es_UY');
        $fechaRapMemFormateada = $carbonFechaRapMem->isoFormat('LL');
        $templateProcessor->setValue('interinato.fechaRapMem', $fechaRapMemFormateada);

        $templateProcessor->setValue('interinato.citeMemSuspencion', $interinato->cite_suspencion_interinato);
        $templateProcessor->setValue('interinato.codigoMemSuspension', $interinato->codigo_suspencion_interinato);

        $carbonFechaSuspencionInterinato = Carbon::parse($interinato->fch_suspencion_interinato);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaSuspencionInterinato->locale('es_UY');
        $fechaSuspencionInterinatoFormateada = $carbonFechaSuspencionInterinato->isoFormat('LL');
        $templateProcessor->setValue('interinato.fchMemSuspencion', $fechaSuspencionInterinatoFormateada);

        $templateProcessor->setValue('interinato.citeInformeInstruccion', $interinato->cite_informe_instruccion_interinato);

        $carbonFechaInformeInstruccion = Carbon::parse($interinato->fch_informe_instruccion_interinato);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaInformeInstruccion->locale('es_UY');
        $fechaInformeInstruccionFormateada = $carbonFechaInformeInstruccion->isoFormat('LL');
        $templateProcessor->setValue('interinato.fechaInformeInstruccion', $fechaInformeInstruccionFormateada);

        $templateProcessor->setValue('interinato.hojaProveido', $interinato->proveido_interinato);
        $templateProcessor->setValue('interinato.numTramite', $interinato->num_tramite_hp_interinato);
        $templateProcessor->setValue('interinato.citeInforme', $interinato->cite_informe_interinato);

        $carbonFechaInformw = Carbon::parse($interinato->fch_cite_informe_interinato);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaInformw->locale('es_UY');
        $fechaInformeFormateada = $carbonFechaInformw->isoFormat('LL');
        $templateProcessor->setValue('interinato.fechaInforme', $fechaInformeFormateada);

        $templateProcessor->setValue('interinato.numFojasInforme', $interinato->num_fojas_informe_interinato);
        $numFojas = $interinato->num_fojas_informe_interinato;
        $numerosEnLetras = [
            0 => 'cero',
            1 => 'uno',
            2 => 'dos',
            3 => 'tres',
            4 => 'cuatro',
            5 => 'cinco',
            6 => 'seis',
            7 => 'siete',
            8 => 'ocho',
            9 => 'nueve',
            10 => 'diez',
            11 => 'once',
            12 => 'doce',
            13 => 'trece',
            14 => 'catorce',
            15 => 'quince',
            16 => 'dieciséis',
            17 => 'diecisiete',
            18 => 'dieciocho',
            19 => 'diecinueve',
            20 => 'veinte',
            30 => 'treinta',
            40 => 'cuarenta',
            50 => 'cincuenta',
            60 => 'sesenta',
            70 => 'setenta',
            80 => 'ochenta',
            90 => 'noventa',
            100 => 'cien',
            200 => 'doscientos',
            300 => 'trescientos',
            400 => 'cuatrocientos',
            500 => 'quinientos',
            600 => 'seiscientos',
            700 => 'setecientos',
            800 => 'ochocientos',
            900 => 'novecientos'
        ];

        if ($numFojas < 0 || $numFojas > 999) {
            $numFojasEnLetras = 'Número fuera de rango';
        } else {
            $numFojasEnLetras = '';

            if ($numFojas >= 100) {
                $centenas = floor($numFojas / 100) * 100;
                $numFojasEnLetras .= $numerosEnLetras[$centenas];
                $numFojas -= $centenas;
                if ($numFojas > 0) {
                    $numFojasEnLetras .= ' ';
                }
            }

            if ($numFojas >= 20) {
                $decenas = floor($numFojas / 10) * 10;
                $numFojasEnLetras .= $numerosEnLetras[$decenas];
                $numFojas -= $decenas;
                if ($numFojas > 0) {
                    $numFojasEnLetras .= ' y ';
                }
            }

            if ($numFojas > 0) {
                $numFojasEnLetras .= $numerosEnLetras[$numFojas];
            }
        }
        $templateProcessor->setValue('interinato.numFojasLetrasInforme', $numFojasEnLetras);  

        $carbonFechaInicioInterinato = Carbon::parse($interinato->fch_inicio_interinato);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaInicioInterinato->locale('es_UY');
        $fechaInicioInterinatoFormateada = $carbonFechaInicioInterinato->isoFormat('LL');
        $templateProcessor->setValue('interinato.fechaInicioInterinato', $fechaInicioInterinatoFormateada);

        $carbonFechaFinInterinato = Carbon::parse($interinato->fch_fin_interinato);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaFinInterinato->locale('es_UY');
        $fechaFinInterinatoFormateada = $carbonFechaFinInterinato->isoFormat('LL');
        $templateProcessor->setValue('interinato.fechaFinInterinato', $fechaFinInterinatoFormateada);

        $templateProcessor->setValue('interinato.nombreUsuarioCreador', $interinato->createdBy->name);
        $templateProcessor->setValue('interinato.denominacionPuestoUsuario', $interinato->createdBy->cargo);   
        $templateProcessor->setValue('interinato.tipoSolicitudInforme', $interinato->tipo_solicitud_informe);

        $nombreCompletoUsuario = $interinato->createdBy->name;
        $partes = explode(' ', $nombreCompletoUsuario);
        $abreviatura = '';
        foreach ($partes as $parte) {
            $abreviatura .= strtoupper($parte[0]);
        }
        $templateProcessor->setValue('interinato.abrevNombreUsuario', $abreviatura);

        $sexo = $interinato->personaActual->genero_persona;
        if ($sexo === 'M') {
            $templateProcessor->setValue('interinato.tituloRespeto', 'Señor');
            $templateProcessor->setValue('interinato.conector', 'al servidor público');
            $templateProcessor->setValue('interinato.conector2', 'del servidor público');
            $templateProcessor->setValue('interinato.conector3', 'el servidor público');
        } else {
            $templateProcessor->setValue('interinato.tituloRespeto', 'Señora');
            $templateProcessor->setValue('interinato.conector', 'a la servidora pública');
            $templateProcessor->setValue('interinato.conector2', 'de la  servidora público');
            $templateProcessor->setValue('interinato.conector3', 'la  servidora público');
        }

        $templateProcessor->setValue('persona.nombreCompleto', $interinato->personaActual->nombre_persona . ' ' . $interinato->personaActual->primer_apellido_persona . ' ' . $interinato->personaActual->segundo_apellido_persona);
        $templateProcessor->setValue('persona.tituloRespeto', $interinato->personaActual->primer_apellido_persona);

        $templateProcessor->setValue('persona.ci', $interinato->personaActual->ci_persona . ' ' . $interinato->personaActual->exp_persona);

        if (isset($interinato->personaActual) && $interinato->personaActual->funcionario->isNotEmpty()) {
            foreach ($interinato->personaActual->funcionario as $funcionario) {
                $codigoFile = $funcionario->codigo_file_funcionario;
                $templateProcessor->setValue('persona.codigoFile', $codigoFile); 
            }
        } else {
            $templateProcessor->setValue('persona.codigoFile', 'Valor no disponible');
        }        

        $templateProcessor->setValue('puestoNuevo.denominacion', $interinato->puestoNuevo->denominacion_puesto);

        $nombreDepartamento = $interinato->puestoNuevo->departamento->nombre_departamento;
        $inicialDepartamento = substr($nombreDepartamento, 0, 1);
        if (in_array($inicialDepartamento, ['D'])) {
            $valorDepartamento = 'del ' . $nombreDepartamento;
        } elseif (in_array($inicialDepartamento, ['G', 'U'])) {
            $valorDepartamento = 'de la ' . $nombreDepartamento;
        } else {
            $valorDepartamento = 'de ' . $nombreDepartamento;
        }
        $templateProcessor->setValue('puestoNuevo.departamentoConector', $valorDepartamento);

        $templateProcessor->setValue('puestoNuevo.departamento', $interinato->puestoNuevo->departamento->nombre_departamento);

        $nombreGerencia = $interinato->puestoNuevo->departamento->gerencia->nombre_gerencia;
        $inicialGerencia = substr($nombreGerencia, 0, 1);
        if (in_array($inicialGerencia, ['P'])) {
            $valorDepartamento = 'de ' . $nombreGerencia;
        } else {
            $valorDepartamento = 'de la ' . $nombreGerencia;
        }
        $templateProcessor->setValue('puestoNuevo.gerenciaConector', $valorDepartamento);

        $templateProcessor->setValue('puestoNuevo.gerencia', $interinato->puestoNuevo->departamento->gerencia->nombre_gerencia);
    }

    public function generarFormInformeCombinado($interinatoId)
    {
        $interinato = Interinato::find($interinatoId);

        if (!isset($interinato)) {
            return response('', 404);
        }

        $disk = Storage::disk('form_templates');

        if (isset($interinato->personaActual) && isset($interinato->puestoNuevo)) {
            if (preg_match('/^G/', $interinato->puestoNuevo->denominacion_puesto)) {
                $pathTemplate = $disk->path('/interinatos/informeCombinadoGerente.docx');
            } else {
                $pathTemplate = $disk->path('/interinatos/informeCombinadoJefe.docx');
            }
        }
        
        $templateProcessor = new TemplateProcessor($pathTemplate);

        $this->valoresComunesByInterinato($templateProcessor, $interinato);

        $fileName = 'INFORME COMBINADO DE ' . mb_strtoupper($interinato->personaActual->nombre_persona) . ' ' . mb_strtoupper($interinato->personaActual->primer_apellido_persona) . ' ' . mb_strtoupper($interinato->personaActual->segundo_apellido_persona) . ' ' . $interinato->descargas;

        $interinato->descargas++;

        $savedPath = $disk->path('/interinatos/generados/') . $fileName . '.docx';

        $templateProcessor->saveAs($savedPath);

        return response()->download($savedPath)->deleteFileAfterSend(true);
    }

    public function generarFormRap($interinatoId)
    {
        $interinato = Interinato::find($interinatoId);

        if (!isset($interinato)) {
            return response('', 404);
        }

        $disk = Storage::disk('form_templates');

        if (isset($interinato->personaActual) && isset($interinato->puestoNuevo)) {
            if (preg_match('/^G/', $interinato->puestoNuevo->denominacion_puesto)) {
                $pathTemplate = $disk->path('/interinatos/rapGerente.docx');
            } else {
                $pathTemplate = $disk->path('/interinatos/rapJefe.docx');
            }
        }

        $templateProcessor = new TemplateProcessor($pathTemplate);

        $this->valoresComunesByInterinato($templateProcessor, $interinato);

        $fileName = 'RAP' . ' ' . strtoupper($interinato->persona->nombre_persona . ' ' . $interinato->persona->primer_apellido_persona . ' ' . $interinato->persona->segundo_apellido_persona) . ' ' . $interinato->descargas;

        $interinato->descargas++;

        $savedPath = $disk->path('/interinatos/generados/') . $fileName . '.docx';

        $templateProcessor->saveAs($savedPath);

        return response()->download($savedPath)->deleteFileAfterSend(true);
    }

    public function generarFormInforme($interinatoId)
    {
        $interinato = Interinato::find($interinatoId);

        if (!isset($interinato)) {
            return response('', 404);
        }

        $disk = Storage::disk('form_templates');

        if (isset($interinato->personaActual) && isset($interinato->puestoNuevo)) {
            if (preg_match('/^G/', $interinato->puestoNuevo->denominacion_puesto)) {
                $pathTemplate = $disk->path('/interinatos/informeGerente.docx');
            } else {
                $pathTemplate = $disk->path('/interinatos/informeJefe.docx');
            }
        }

        $templateProcessor = new TemplateProcessor($pathTemplate);

        $this->valoresComunesByInterinato($templateProcessor, $interinato);

        $fileName = 'INFORME' . ' ' . strtoupper($interinato->persona->nombre_persona . ' ' . $interinato->persona->primer_apellido_persona . ' ' . $interinato->persona->segundo_apellido_persona) . ' ' . $interinato->descargas;

        $interinato->descargas++;

        $savedPath = $disk->path('/interinatos/generados/') . $fileName . '.docx';

        $templateProcessor->saveAs($savedPath);

        return response()->download($savedPath)->deleteFileAfterSend(true);
    }

    public function generarFormMemorandum($interinatoId)
    {
        $interinato = Interinato::find($interinatoId);

        if (!isset($interinato)) {
            return response('', 404);
        }

        $disk = Storage::disk('form_templates');

        if (isset($interinato->personaActual) && isset($interinato->puestoNuevo)) {
            if (preg_match('/^G/', $interinato->puestoNuevo->denominacion_puesto)) {
                $pathTemplate = $disk->path('/interinatos/memorandumGerente.docx');
            } else {
                $pathTemplate = $disk->path('/interinatos/memorandumJefe.docx');
            }
        }

        $templateProcessor = new TemplateProcessor($pathTemplate);

        $this->valoresComunesByInterinato($templateProcessor, $interinato);

        $fileName = 'MEMORANDUM' . ' ' . strtoupper($interinato->persona->nombre_persona . ' ' . $interinato->persona->primer_apellido_persona . ' ' . $interinato->persona->segundo_apellido_persona) . ' ' . $interinato->descargas;

        $interinato->descargas++;

        $savedPath = $disk->path('/interinatos/generados/') . $fileName . '.docx';

        $templateProcessor->saveAs($savedPath);

        return response()->download($savedPath)->deleteFileAfterSend(true);
    }

    public function generarFormMemSuspencion($interinatoId)
    {
        $interinato = Interinato::find($interinatoId);

        if (!isset($interinato)) {
            return response('', 404);
        }

        $disk = Storage::disk('form_templates');

        $pathTemplate = $disk->path('/interinatos/memoradunSuspencion.docx');
        
        $templateProcessor = new TemplateProcessor($pathTemplate);

        $this->valoresComunesByInterinato($templateProcessor, $interinato);

        $fileName = 'MEM SUSPENCION ' . mb_strtoupper($interinato->personaActual->nombre_persona) . ' ' . mb_strtoupper($interinato->personaActual->primer_apellido_persona) . ' ' . mb_strtoupper($interinato->personaActual->segundo_apellido_persona) . ' ' . $interinato->descargas;

        $interinato->descargas++;

        $savedPath = $disk->path('/interinatos/generados/') . $fileName . '.docx';

        $templateProcessor->saveAs($savedPath);

        return response()->download($savedPath)->deleteFileAfterSend(true);
    }

    public function mostrarInterinato($id)
    {
        $interinato = Interinato::with([
            'puestoNuevo.departamento.gerencia',
            'puestoActual.departamento.gerencia',
            'personaActual'
        ])->findOrFail($id);
        return response()->json($interinato);
    }

    public function modificarInterinato(Request $request)
    {
        $validatedData = $request->validate([
            'idInterinato' => 'required|integer',
            'puestoNuevoId' => 'required|integer',
            'puestoActualId' => 'required|integer',
            'personaId' => 'required|integer',
            'fchInicioInterinato' => 'required|date',
            'fchFinInterinato' => 'required|date',

            'citeInformeInstruccionInterinato' => 'nullable|string',
            'fchInformeInstruccionInterinato' => 'nullable|date',
            'proveidoInterinato' => 'nullable|string',
            'numTramiteHpInterinato' => 'nullable|string',
            'citeInformeInterinato' => 'nullable|string',
            'fchCiteInformeInterinato' => 'nullable|date',
            'numFojasInformeInterinato' => 'nullable|integer',
            'tipoSolicitudInforme' => 'nullable|integer',

            'citeRapInterinato' => 'nullable|string',
            'codigoRapInterinato' => 'nullable|string',
            'numFojasRapInterinato' => 'nullable|integer',

            'citeMemInterinato' => 'nullable|string',
            'codigoMemInterinato' => 'nullable|string',
            'codigoFileInterinato' => 'nullable|string',
            'fchMemorandumRapInterinato' => 'nullable|date',

            'citeSuspencionInterinato' => 'nullable|string',
            'codigoSuspencionInterinato' => 'nullable|string',
            'fchSuspencionInterinato' => 'nullable|date',

            'modifiedInterinato' => 'required|integer',
        ]);

        $id = $validatedData['idInterinato'];

        $validatedData['fchInformeInstruccionInterinato'] = date('Y-m-d H:i:s', strtotime($validatedData['fchInformeInstruccionInterinato']));
        $validatedData['fchCiteInformeInterinato'] = date('Y-m-d H:i:s', strtotime($validatedData['fchCiteInformeInterinato']));
        $validatedData['fchMemorandumRapInterinato'] = date('Y-m-d H:i:s', strtotime($validatedData['fchMemorandumRapInterinato']));
        $validatedData['fchSuspencionInterinato'] = date('Y-m-d H:i:s', strtotime($validatedData['fchSuspencionInterinato']));

        if (
            !empty($validatedData['citeSuspencionInterinato']) &&
            !empty($validatedData['codigoSuspencionInterinato']) &&
            !empty($validatedData['fchSuspencionInterinato'])
        ) {
            $updated = Interinato::where('id_interinato', $id)->update([

                'cite_informe_instruccion_interinato' => $validatedData['citeInformeInstruccionInterinato'],
                'fch_informe_instruccion_interinato' => $validatedData['fchInformeInstruccionInterinato'],
                'proveido_interinato' => $validatedData['proveidoInterinato'],
                'num_tramite_hp_interinato' => $validatedData['numTramiteHpInterinato'],
                'cite_informe_interinato' => $validatedData['citeInformeInterinato'],
                'fch_cite_informe_interinato' => $validatedData['fchCiteInformeInterinato'],
                'num_fojas_informe_interinato' => $validatedData['numFojasInformeInterinato'],
                'tipo_solicitud_informe' => $validatedData['tipoSolicitudInforme'],

                'cite_rap_interinato' => $validatedData['citeRapInterinato'],
                'codigo_rap_interinato' => $validatedData['codigoRapInterinato'],
                'num_fojas_rap_interinato' => $validatedData['numFojasRapInterinato'],

                'cite_mem_interinato' => $validatedData['citeMemInterinato'],
                'codigo_mem_interinato' => $validatedData['codigoMemInterinato'],
                'codigo_file_interinato' => $validatedData['codigoFileInterinato'],
                'fch_memorandum_rap_interinato' => $validatedData['fchMemorandumRapInterinato'],

                'cite_suspencion_interinato' => $validatedData['citeSuspencionInterinato'],
                'codigo_suspencion_interinato' => $validatedData['codigoSuspencionInterinato'],
                'fch_suspencion_interinato' => $validatedData['fchSuspencionInterinato'],

                'estado_interinato' => 3,
            ]);
        } else {
            $updated = Interinato::where('id_interinato', $id)->update([

                'cite_informe_instruccion_interinato' => $validatedData['citeInformeInstruccionInterinato'],
                'fch_informe_instruccion_interinato' => $validatedData['fchInformeInstruccionInterinato'],
                'proveido_interinato' => $validatedData['proveidoInterinato'],
                'num_tramite_hp_interinato' => $validatedData['numTramiteHpInterinato'],
                'cite_informe_interinato' => $validatedData['citeInformeInterinato'],
                'fch_cite_informe_interinato' => $validatedData['fchCiteInformeInterinato'],
                'num_fojas_informe_interinato' => $validatedData['numFojasInformeInterinato'],
                'tipo_solicitud_informe' => $validatedData['tipoSolicitudInforme'],

                'cite_rap_interinato' => $validatedData['citeRapInterinato'],
                'codigo_rap_interinato' => $validatedData['codigoRapInterinato'],
                'num_fojas_rap_interinato' => $validatedData['numFojasRapInterinato'],

                'cite_mem_interinato' => $validatedData['citeMemInterinato'],
                'codigo_mem_interinato' => $validatedData['codigoMemInterinato'],
                'codigo_file_interinato' => $validatedData['codigoFileInterinato'],
                'fch_memorandum_rap_interinato' => $validatedData['fchMemorandumRapInterinato'],
            ]);
        }

        if (isset($updated) && $updated) {
            return response()->json(['message' => 'Interinato actualizado correctamente']);
        } else {
            return response()->json(['message' => 'No se encontró el interinato para actualizar'], 404);
        }
    }

    public function darBajaInterinato(Request $request, $interinatoId)
    {
        $interinato = Interinato::find($interinatoId);

        if (!$interinato) {
            return response()->json(['message' => 'Interinato no encontrado.'], 404);
        }

        $modifiedInterinato = $request->input('modifiedInterinato');
        $interinato->modified_interinato = $modifiedInterinato;

        $interinato->estado_interinato = 4;

        if ($interinato->save()) {
            return response()->json(['message' => 'Interinato dado de baja exitosamente.'], 200);
        } else {
            return response()->json(['message' => 'Error al dar de baja el interinato.'], 500);
        }
    }

    public function uploadInterinato(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $filePath = $request->file('file')->store('uploads');
        $spreadsheet = IOFactory::load(storage_path('app/' . $filePath));

        $sheetNames = $spreadsheet->getSheetNames();
        $designacionesSheetIndex = array_search('DESIGNACIONES', $sheetNames);

        if ($designacionesSheetIndex === false) {
            return response()->json(['message' => 'Hoja "DESIGNACIONES" no encontrada.'], 404);
        }

        $sheet = $spreadsheet->getSheet($designacionesSheetIndex);

        foreach ($sheet->getRowIterator(2) as $row) {
            $data = [];
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            // Leer los valores de las celdas
            $cellIterator->next();  // a
            $data['cite_informe_instruccion_interinato'] = $cellIterator->current()->getValue(); // b
            $data['fch_informe_instruccion_interinato'] = $cellIterator->current()->getValue(); // c
            $cellIterator->next(); // d

            $itemDestino = $cellIterator->current()->getValue();  // e
            $puestoNuevo = Puesto::where('item_puesto', $itemDestino)->first();
            $data['puesto_nuevo_id'] = $puestoNuevo ? $puestoNuevo->id : null;

            for ($i = 0; $i < 7; $i++) {
                $cellIterator->next();
            }
            $itemActual = $cellIterator->current()->getValue(); // N
            $puestoActual = Puesto::where('item_puesto', $itemActual)->first();
            $data['puesto_actual_id'] = $puestoActual ? $puestoActual->id : null;

            for ($i = 0; $i < 8; $i++) {
                $cellIterator->next();
            }
            $ciPersona = $cellIterator->current()->getValue(); // W
            $persona = Persona::where('ci_persona', $ciPersona)->first();
            $data['persona_id'] = $persona ? $persona->id : null;

            for ($i = 0; $i < 3; $i++) {
                $cellIterator->next();
            }

            // Leer los siguientes valores
            $data['cite_informe_interinato'] = $cellIterator->current()->getValue(); // AA
            $data['num_fojas_informe_interinato'] = $cellIterator->current()->getValue(); // AB
            $data['cite_mem_interinato'] = $cellIterator->current()->getValue(); // AC
            $data['codigo_mem_interinato'] = $cellIterator->current()->getValue(); // AD
            $data['cite_rap_interinato'] = $cellIterator->current()->getValue(); // AE
            $data['codigo_rap_interinato'] = $cellIterator->current()->getValue(); // AF
            $data['fch_memorandum_rap_internato'] = $cellIterator->current()->getValue(); // AG
            $cellIterator->next();
            $data['fch_inicio_interinato'] = $cellIterator->current()->getValue(); // AI
            $data['fch_fin_interinato'] = $cellIterator->current()->getValue(); // AJ

            // Log de los datos leídos
            Log::info('Datos leídos:', $data);

            // Validar la fecha de finalización
            $fchFinInterinato = $data['fch_fin_interinato'];

            if (empty($fchFinInterinato) || (is_string($fchFinInterinato) && strpos($fchFinInterinato, '=') === 0)) {
                continue;
            }

            try {
                $fchFinInterinato = \Carbon\Carbon::createFromFormat('d/m/Y', $fchFinInterinato);
            } catch (\Exception $e) {
                continue;
            }

            if ($fchFinInterinato->lt(now())) {
                continue;
            }

            for ($i = 0; $i < 4; $i++) {
                $cellIterator->next();
            }
            $data['tipo_solicitud_informe'] = $cellIterator->current()->getValue(); // AO

            // Log de los datos que se van a insertar
            Log::info('Datos a insertar en Interinato:', $data);

            Interinato::create($data);
        }

        return response()->json(['message' => 'Datos subidos correctamente.']);
    }






    public function  exportInterinatoExcel()
    {
        return Excel::download(new InterinatosExport, 'Reporte_Interinatos.xlsx');
    }
}

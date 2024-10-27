<?php

namespace App\Http\Controllers;

use App\Imports\InterinatoDataImport;
use App\Models\Interinato;
use App\Models\Persona;
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
use PhpOffice\PhpWord\TemplateProcessor;
use Carbon\Carbon;


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

        $templateProcessor->setValue('interinato.citeInformeInstruccion', $interinato->cite_informe_instruccion_interinato);
        $carbonFechaInformeInstruccion = Carbon::parse($interinato->fch_informe_instruccion_interinato);
        setlocale(LC_TIME, 'es_UY');
        $carbonFechaInformeInstruccion->locale('es_UY');
        $fechaInformeInstruccionFormateada = $carbonFechaInformeInstruccion->isoFormat('LL');
        $templateProcessor->setValue('incorporacion.fechaInformeInstruccion', $fechaInformeInstruccionFormateada);
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

        $templateProcessor->setValue('persona.nombreCompleto', $interinato->persona->nombre_persona . ' ' . $interinato->persona->primer_apellido_persona . ' ' . $interinato->persona->segundo_apellido_persona);
        $templateProcessor->setValue('persona.nombreCompleto', $interinato->persona->primer_apellido_persona);

        $templateProcessor->setValue('persona.ci', $interinato->persona->ci_persona . ' ' . $interinato->persona->exp_persona);

        $templateProcessor->setValue('puestoNuevo.denominacion', $interinato->puesto_nuevo->denominacion_puesto);

        $nombreDepartamento = $interinato->puesto_nuevo->departamento->nombre_departamento;
        $inicialDepartamento = substr($nombreDepartamento, 0, 1);
        if (in_array($inicialDepartamento, ['D'])) {
            $valorDepartamento = 'del ' . $nombreDepartamento;
        } elseif (in_array($inicialDepartamento, ['G', 'U'])) {
            $valorDepartamento = 'de la ' . $nombreDepartamento;
        } else {
            $valorDepartamento = 'de ' . $nombreDepartamento;
        }
        $templateProcessor->setValue('puestoNuevo.departamentoConector', $valorDepartamento);

        $nombreGerencia = $interinato->puesto_nuevo->departamento->gerencia->nombre_gerencia;
        $inicialGerencia = substr($nombreGerencia, 0, 1);
        if (in_array($inicialGerencia, ['P'])) {
            $valorDepartamento = 'de ' . $nombreGerencia;
        } else {
            $valorDepartamento = 'de la ' . $nombreGerencia;
        }
        $templateProcessor->setValue('puestoNuevo.gerenciaConector', $valorDepartamento);

        $templateProcessor->setValue('puestoNuevo.gerencia', $interinato->puesto_nuevo->departamento->gerencia->nombre_gerencia);

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
        $templateProcessor->setValue('interinato.denominacioPuestoUsuario', $interinato->createdBy->cargo);

        $nombreCompletoUsuario = $interinato->createdBy->name;
        $partes = explode(' ', $nombreCompletoUsuario);
        $abreviatura = '';
        foreach ($partes as $parte) {
            $abreviatura .= strtoupper($parte[0]);
        }
        $templateProcessor->setValue('interinato.abrevNombreUsuario', $abreviatura);

        $sexo = $interinato->persona->genero_persona;
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
    }

    public function generarFormInformeCombinado($interinatoId)
    {
        $interinato = Interinato::find($interinatoId);

        if (!isset($interinato)) {
            return response('', 404);
        }

        $disk = Storage::disk('form_templates_interinato');



        if (isset($interinato->puesto_actual) && isset($interinato->puesto_nuevo)) {
            if (preg_match('/^(Gerente)/', $interinato->puesto_nuevo->denominacion_puesto)) {
                $pathTemplate = $disk->path('informeCombinadoGerente.docx');
            } else {
                $pathTemplate = $disk->path('rapJefe.docx');
            }
        }

        $templateProcessor = new TemplateProcessor($pathTemplate);

        $this->valoresComunesByInterinato($templateProcessor, $interinato);

        $fileName = 'RAP' . ' ' . strtoupper($interinato->persona->nombre_persona . ' ' . $interinato->persona->primer_apellido_persona . ' ' . $interinato->persona->segundo_apellido_persona) . ' ' . $interinato->descargas;

        $interinato->descargas++;

        $savedPath = $disk->path('generados/') . $fileName . '.docx';

        $templateProcessor->saveAs($savedPath);

        return response()->download($savedPath)->deleteFileAfterSend(true);
    }


    public function generarFormRap($interinatoId)
    {
        $interinato = Interinato::find($interinatoId);

        if (!isset($interinato)) {
            return response('', 404);
        }

        $disk = Storage::disk('form_templates_interinato');

        if (isset($interinato->puesto_actual) && isset($interinato->puesto_nuevo)) {
            if (preg_match('/^(Gerente)/', $interinato->puesto_nuevo->denominacion_puesto)) {
                $pathTemplate = $disk->path('rapGerente.docx');
            } else {
                $pathTemplate = $disk->path('rapJefe.docx');
            }
        }

        $templateProcessor = new TemplateProcessor($pathTemplate);

        $this->valoresComunesByInterinato($templateProcessor, $interinato);

        $fileName = 'RAP' . ' ' . strtoupper($interinato->persona->nombre_persona . ' ' . $interinato->persona->primer_apellido_persona . ' ' . $interinato->persona->segundo_apellido_persona) . ' ' . $interinato->descargas;

        $interinato->descargas++;

        $savedPath = $disk->path('generados/') . $fileName . '.docx';

        $templateProcessor->saveAs($savedPath);

        return response()->download($savedPath)->deleteFileAfterSend(true);
    }

    public function generarFormInforme($interinatoId)
    {
        $interinato = Interinato::find($interinatoId);

        if (!isset($interinato)) {
            return response('', 404);
        }

        $disk = Storage::disk('form_templates_interinato');

        if (isset($interinato->puesto_actual) && isset($interinato->puesto_nuevo)) {
            if (preg_match('/^(Gerente)/', $interinato->puesto_nuevo->denominacion_puesto)) {
                $pathTemplate = $disk->path('informeGerente.docx');
            } else {
                $pathTemplate = $disk->path('informeJefe.docx');
            }
        }

        $templateProcessor = new TemplateProcessor($pathTemplate);

        $this->valoresComunesByInterinato($templateProcessor, $interinato);

        $fileName = 'RAP' . ' ' . strtoupper($interinato->persona->nombre_persona . ' ' . $interinato->persona->primer_apellido_persona . ' ' . $interinato->persona->segundo_apellido_persona) . ' ' . $interinato->descargas;

        $interinato->descargas++;

        $savedPath = $disk->path('generados/') . $fileName . '.docx';

        $templateProcessor->saveAs($savedPath);

        return response()->download($savedPath)->deleteFileAfterSend(true);
    }

    public function generarFormMemorandum($interinatoId)
    {
        $interinato = Interinato::find($interinatoId);

        if (!isset($interinato)) {
            return response('', 404);
        }

        $disk = Storage::disk('form_templates_interinato');

        if (isset($interinato->puesto_actual) && isset($interinato->puesto_nuevo)) {
            if (preg_match('/^(Gerente)/', $interinato->puesto_nuevo->denominacion_puesto)) {
                $pathTemplate = $disk->path('memorandumGerente.docx');
            } else {
                $pathTemplate = $disk->path('memorandumJefe.docx');
            }
        }

        $templateProcessor = new TemplateProcessor($pathTemplate);

        $this->valoresComunesByInterinato($templateProcessor, $interinato);

        $fileName = 'RAP' . ' ' . strtoupper($interinato->persona->nombre_persona . ' ' . $interinato->persona->primer_apellido_persona . ' ' . $interinato->persona->segundo_apellido_persona) . ' ' . $interinato->descargas;

        $interinato->descargas++;

        $savedPath = $disk->path('generados/') . $fileName . '.docx';

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

        if (
            !empty($validatedData['citeSuspencionInterinato']) &&
            !empty($validatedData['codigoSuspencionInterinato']) &&
            !empty($validatedData['fchSuspencionInterinato'])
        ) {

            Interinato::create([
                'cite_informe_instruccion_interinato' => $validatedData['citeInformeInstruccionInterinato'],
                'fch_informe_instruccion_interinato' => $validatedData['fchInformeInstruccionInterinato'],
                'proveido_interinato' => $validatedData['proveidoInterinato'],
                'num_tramite_hp_interinato' => $validatedData['numTramiteHpInterinato'],
                'cite_informe_interinato' => $validatedData['citeInformeInterinato'],
                'fch_cite_informe_interinato' => $validatedData['fchCiteInformeInterinato'],
                'num_fojas_informe_interinato' => $validatedData['numFojasInformeInterinato'],

                'cite_rap_interinato' => $validatedData['citeRapInterinato'],
                'codigo_rap_interinato' => $validatedData['codigoRapInterinato'],
                'num_fojas_rap_interinato' => $validatedData['numFojasRapInterinato'],

                'cite_mem_interinato' => $validatedData['citeMemInterinato'],
                'codigo_mem_interinato' => $validatedData['codigoMemInterinato'],
                'fch_memorandum_rap_interinato' => $validatedData['fchMemorandumRapInterinato'],

                'cite_suspencion_interinato' => $validatedData['citeSuspencionInterinato'],
                'codigoSuspencionInterinato' => $validatedData['codigoSuspencionInterinato'],
                'fch_memorandum_rap_interinato' => $validatedData['fchMemorandumRapInterinato'],

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

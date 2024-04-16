<?php

namespace App\Http\Controllers;

use App\Models\AreaFormaci_personaon;
use App\Models\GradoAcademico;
use App\Models\Incorporacion;
use App\Models\Institucion;
use App\Models\Persona;
use App\Models\Puesto;
use App\Models\Departamento;
use App\Models\Gerencia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;
use PhpOffice\PhpWord\TemplateProcessor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class IncorporacionesController extends Controller
{

  public function crearActualizarIncorporacion(Request $request)
  {
    $validatedData = $request->validate([
      'idIncorporacion' => 'nullable|integer',
      'puestoNuevoId' => 'integer',
      'personaId' => 'nullable|integer',
      'fchIncorporacion' => 'nullable|string',
      'hpIncorporacion' => 'nullable|string',
      'observacionIncorporacion' => 'nullable|string',
      // Experiencia de la incorporacion
      'cumpleExpProfesionalIncorporacion' => 'nullable|integer',
      'cumpleExpEspecificaIncorporacion' => 'nullable|integer',
      'cumpleExpMandoIncorporacion' => 'nullable|integer',
      'cumpleFormacionIncorporacion' => 'nullable|integer',
      // Datos de cites y fechas
      'citeNotaMinutaIncorporacion' => 'nullable|string',
      'codigoNotaMinutaIncorporacion' => 'nullable|string',
      'fchNotaMinutaIncorporacion' => 'nullable|string',
      'fchRecepcionNotaIncorporacion' => 'nullable|string',

      'citeInformeIncorporacion' => 'nullable|string',
      'fchInformeIncorporacion' => 'nullable|string',

      'citeMemorandumIncorporacion' => 'nullable|string',
      'codigoMemorandumIncorporacion' => 'nullable|string',
      'fchMemorandumIncorporacion' => 'nullable|string',

      'citeRapIncorporacion' => 'nullable|string',
      'codigoRapIncorporacion' => 'nullable|string',
      'fchRapIncorporacion' => 'nullable|string',

    ]);

    if (isset($validatedData['idIncorporacion']))
      $incorporacion = Incorporacion::find($validatedData['idIncorporacion']);
    else
      $incorporacion = new Incorporacion();

    // agregar campos para actualizacion
    if (isset($validatedData['personaId'])) {
      $incorporacion->persona_id = $validatedData['personaId'];
    }

    if (isset($validatedData['puestoNuevoId'])) {
      $incorporacion->puesto_nuevo_id = $validatedData['puestoNuevoId'];
    }

    if (isset($validatedData['fchIncorporacion'])) {
      $incorporacion->fch_incorporacion = Carbon::parse($validatedData['fchIncorporacion'])->format('Y-m-d');
    }
    if (isset($validatedData['hpIncorporacion'])) {
      $incorporacion->hp_incorporacion = $validatedData['hpIncorporacion'];
    }

    if (isset($validatedData['observacionIncorporacion'])) {
      $incorporacion->observacion_incorporacion = $validatedData['observacionIncorporacion'];
    }

    /* ------------------------------- DATOS DE SI CUEMPLE LA PERSONA ------------------------------- */

    if (isset($validatedData['cumpleExpProfesionalIncorporacion'])) {
      $incorporacion->cumple_exp_profesional_incorporacion = $validatedData['cumpleExpProfesionalIncorporacion'];
    }
    if (isset($validatedData['cumpleExpEspecificaIncorporacion'])) {
      $incorporacion->cumple_exp_especifica_incorporacion = $validatedData['cumpleExpEspecificaIncorporacion'];
    }
    if (isset($validatedData['cumpleExpMandoIncorporacion'])) {
      $incorporacion->cumple_exp_mando_incorporacion = $validatedData['cumpleExpMandoIncorporacion'];
    }
    if (isset($validatedData['cumpleFormacionIncorporacion'])) {
      $incorporacion->cumple_formacion_incorporacion = $validatedData['cumpleFormacionIncorporacion'];
    }

    /* ----------------------------------- DATOS DE CITES Y FECHAS ---------------------------------- */
    if (isset($validatedData['citeNotaMinutaIncorporacion'])) {
      $incorporacion->cite_nota_minuta_incorporacion = $validatedData['citeNotaMinutaIncorporacion'];
    }

    if (isset($validatedData['codigoNotaMinutaIncorporacion'])) {
      $incorporacion->codigo_nota_minuta_incorporacion = $validatedData['codigoNotaMinutaIncorporacion'];
    }

    if (isset($validatedData['fchNotaMinutaIncorporacion'])) {
      $incorporacion->fch_recepcion_nota_incorporacion = Carbon::parse($validatedData['fchNotaMinutaIncorporacion'])->format('Y-m-d');
    }

    if (isset($validatedData['fchRecepcionNotaIncorporacion'])) {
      $incorporacion->fch_recepcion_nota_incorporacion = Carbon::parse($validatedData['fchRecepcionNotaIncorporacion'])->format('Y-m-d');
    }

    if (isset($validatedData['citeInformeIncorporacion'])) {
      $incorporacion->cite_informe_incorporacion = $validatedData['citeInformeIncorporacion'];
    }

    if (isset($validatedData['fchInformeIncorporacion'])) {
      $incorporacion->fch_informe_incorporacion = Carbon::parse($validatedData['fchInformeIncorporacion'])->format('Y-m-d');
    }

    if (isset($validatedData['citeMemorandumIncorporacion'])) {
      $incorporacion->cite_memorandum_incorporacion = $validatedData['citeMemorandumIncorporacion'];
    }

    if (isset($validatedData['codigoMemorandumIncorporacion'])) {
      $incorporacion->codigo_memorandum_incorporacion = $validatedData['codigoMemorandumIncorporacion'];
    }

    if (isset($validatedData['fchMemorandumIncorporacion'])) {
      $incorporacion->fch_memorandum_incorporacion = Carbon::parse($validatedData['fchMemorandumIncorporacion'])->format('Y-m-d');
    }

    if (isset($validatedData['citeRapIncorporacion'])) {
      $incorporacion->cite_rap_incorporacion = $validatedData['citeRapIncorporacion'];
    }

    if (isset($validatedData['codigoRapIncorporacion'])) {
      $incorporacion->codigo_rap_incorporacion = $validatedData['codigoRapIncorporacion'];
    }

    if (isset($validatedData['fchRapIncorporacion'])) {
      $incorporacion->fch_rap_incorporacion = Carbon::parse($validatedData['fchRapIncorporacion'])->format('Y-m-d');
    }

    // guardar
    $incorporacion->save();

    $incorporacion->estado_incorporacion = 2;
    $incorporacion->save();

    return $this->sendObject($incorporacion);
  }

  public function listPaginateIncorporaciones(Request $request)
  {
    $limit = $request->input('limit', 10);
    $page = $request->input('page', 0);
    $query = Incorporacion::with([
      'persona',
      // 'puesto_actual.departamento.Gerencia',
      //'puesto_nuevo.departamento.gerencia',
      // 'puesto_nuevo.persona_actual',
      // 'puesto_nuevo.Funcionario.persona',
      // 'puesto_nuevo.requisitos'

        'puesto_nuevo:id_puesto,item_puesto,departamento_id', 
        'puesto_nuevo.departamento:id_departamento,nombre_departamento,gerencia_id',
        'puesto_nuevo.departamento.gerencia:id_gerencia,nombre_gerencia',
    ]);
    $incorporaciones = $query->paginate($limit, ['*'], 'page', $page);
    // $incorporaciones->data;
    return $this->sendPaginated($incorporaciones);
  }

  //genera el words R-0078 Nueva Incorporacion
  public function generarFormularioEvalR0078($incorporacionId)
  {
    $incorporacion = Incorporacion::find($incorporacionId);
    if (!isset($incorporacion)) {
      return response('', 404);
    }

    $disk = Storage::disk('form_templates');
    $pathTemplate = $disk->path('R-0078-01.docx'); // ruta de plantilla
    $templateProcessor = new TemplateProcessor($pathTemplate);

    $templateProcessor->setValue('persona.nombreCompleto', $incorporacion->persona->nombre_persona . ' ' . $incorporacion->persona->primer_apellido_persona . ' ' . $incorporacion->persona->segundo_apellido_persona);

    $templateProcessor->setValue('persona.profesion', $incorporacion->persona->profesion_persona);

    $fechaNacimiento = Carbon::parse($incorporacion->persona->fch_nacimiento_persona);
    $edad = $fechaNacimiento->age;
    $templateProcessor->setValue('persona.edad', $edad);

    $templateProcessor->setValue('puesto_nuevo.item', $incorporacion->puesto_nuevo->item_puesto);

    $templateProcessor->setValue('puesto_nuevo.gerencia', $incorporacion->puesto_nuevo->departamento->gerencia->nombre_gerencia);

    $templateProcessor->setValue('puesto_nuevo.departamento', $incorporacion->puesto_nuevo->departamento->nombre_departamento);

    $templateProcessor->setValue('puesto_nuevo.denominacion', $incorporacion->puesto_nuevo->denominacion_puesto);

    $templateProcessor->setValue('puesto_nuevo.salario', $incorporacion->puesto_nuevo->salario_puesto);

    foreach ($incorporacion->puesto_nuevo->requisitos as $requisito) {
      if ($requisito) {
        $templateProcessor->setValue('puesto_nuevo.formacionRequerida', $requisito->formacion_requisito);
        $templateProcessor->setValue('puesto_nuevo.experienciaProfesionalSegunCargo', $requisito->exp_cargo_requisito);
        $templateProcessor->setValue('puesto_nuevo.experienciaRelacionadoAlArea', $requisito->exp_area_requisito);
        $templateProcessor->setValue('puesto_nuevo.experienciaEnFuncionesDeMando', $requisito->exp_mando_requisito);
        break;
      }
    }

    $templateProcessor->setValue('incorporacion.observacion', strtoupper($incorporacion->observacion_incorporacion));
    $fileName = 'R-0078_' . $incorporacion->persona->nombre_persona;
    $savedPath = $disk->path('generados/') . $fileName . '.docx';
    $templateProcessor->saveAs($savedPath);
    // return response()->json(['incorporacion' => $incorporacion, 'filePath' => $fileName . '.docx']);
    return response()->download($savedPath)->deleteFileAfterSend(true);
  }
  
  //1401
  public function genFormEvalR1401($incorporacionId)
  {
    $incorporacion = Incorporacion::find($incorporacionId);

    if (!isset($incorporacion)) {
      return response('', 404);
    }

    $disk = Storage::disk('form_templates');
    $pathTemplate = $disk->path('R-1401-01.docx');
    $templateProcessor = new TemplateProcessor($pathTemplate);

    $templateProcessor->setValue('persona.nombreCompleto', $incorporacion->persona->nombre_persona . ' ' . $incorporacion->persona->primer_apellido_persona . ' ' . $incorporacion->persona->segundo_apellido_persona);
    $templateProcessor->setValue('persona.ci', $incorporacion->persona->ci_persona);
    $templateProcessor->setValue('persona.exp', $incorporacion->persona->exp_persona);

    $nombreGerencia = $incorporacion->puesto_nuevo->departamento->gerencia->nombre_gerencia; //estoooooooo ver 

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

    $fileName = 'R-1401_' . $incorporacion->persona->nombre_persona;
    $savedPath = $disk->path('generados/') . $fileName . '.docx';
    $templateProcessor->saveAs($savedPath);
    // return response()->json(['incorporacion' => $incorporacion, 'filePath' => $fileName . '.docx']);
    return response()->download($savedPath)->deleteFileAfterSend(true);
  }

   //para informe con nota
   public function genFormInformeNota($incorporacionId)
   {
       $incorporacion = Incorporacion::find($incorporacionId);

       if (!isset($incorporacion)) {
           return response('', 404);
       }

       $incorporacion->incorporacion_estado = 3;
       $incorporacion->save();

       $disk = Storage::disk('form_templates');
       if (isset($incorporacion->puesto_actual)) {
           $pathTemplate = $disk->path('InfNotaCambioItem.docx'); // ruta de plantilla
       } else {
           $pathTemplate = $disk->path('informenota.docx');
       }
       $templateProcessor = new TemplateProcessor($pathTemplate);

       $templateProcessor->setValue('incorporacion.citeInforme', $incorporacion->cite_informe);

       $nombreCompleto = $incorporacion->persona->nombre_completo;
       $sexo = $incorporacion->persona->sexo;
       if ($sexo === 'F') {
           $templateProcessor->setValue('persona.referenciaMayuscula', 'DE LA SERVIDORA PÚBLICA INTERINA ' . mb_strtoupper($nombreCompleto, 'UTF-8'));
           $templateProcessor->setValue('persona.referenciaMayuscula1', 'SERVIDORA PÚBLICA INTERINA DE LA SEÑORA ' . mb_strtoupper($nombreCompleto, 'UTF-8'));
           $templateProcessor->setValue('persona.referencia', 'de la servidora publica interina ' . $nombreCompleto);
           $templateProcessor->setValue('persona.referencia1', ' servidora publica interina de la señora ' . $nombreCompleto);
           $templateProcessor->setValue('persona.referenciaAlPrincipio', 'La servidora publica interina ' . $nombreCompleto);
           $templateProcessor->setValue('persona.referenciaAlPrincipio1', 'La señora ' . $nombreCompleto);
       } else {
           $templateProcessor->setValue('persona.referenciaMayuscula', 'DEL SERVIDOR PÚBLICO INTERINO ' . mb_strtoupper($nombreCompleto, 'UTF-8'));
           $templateProcessor->setValue('persona.referenciaMayuscula1', 'SERVIDOR PÚBLICO INTERINO DEL SEÑOR ' . mb_strtoupper($nombreCompleto, 'UTF-8'));
           $templateProcessor->setValue('persona.referencia', 'del servidor publico interino ' . $nombreCompleto);
           $templateProcessor->setValue('persona.referencia1', 'servidor publico interino del señor ' . $nombreCompleto);
           $templateProcessor->setValue('persona.referenciaAlPrincipio', 'El servidor publico interino ' . $nombreCompleto);
           $templateProcessor->setValue('persona.referenciaAlPrincipio1', 'El señor ' . $nombreCompleto);
       }

       if ($incorporacion->puesto_actual) {
           $templateProcessor->setValue('puesto_actual.item', $incorporacion->puesto_actual->item);

           $denominacion = isset($incorporacion->puesto_actual->denominacion) ? $incorporacion->puesto_actual->denominacion : 'Valor predeterminado o mensaje de error';

           $templateProcessor->setValue('puesto_actual.denominacionMayuscula', mb_strtoupper($denominacion, 'UTF-8'));

           $nombreDepartamento = mb_strtoupper($incorporacion->puesto_actual->departamento->nombre, 'UTF-8');
           $inicialDepartamento = mb_strtoupper(substr($nombreDepartamento, 0, 1), 'UTF-8');

           if (in_array($inicialDepartamento, ['D'])) {
               $valorDepartamento = 'DEL ' . $nombreDepartamento;
           } elseif (in_array($inicialDepartamento, ['G', 'A', 'U', 'P'])) {
               $valorDepartamento = 'DE LA ' . $nombreDepartamento;
           } else {
               $valorDepartamento = 'DE ' . $nombreDepartamento;
           }

           $templateProcessor->setValue('puesto_actual.departamentoMayuscula', $valorDepartamento);

           $templateProcessor->setValue('puesto_actual.gerenciaMayuscula', mb_strtoupper($incorporacion->puesto_actual->departamento->gerencia->nombre, 'UTF-8'));
       } else {
           $templateProcessor->setValue('puesto_actual.item', 'Valor predeterminado o mensaje de error');
           $templateProcessor->setValue('puesto_actual.denominacionMayuscula', 'Valor predeterminado o mensaje de error');
           $templateProcessor->setValue('puesto_actual.departamentoMayuscula', 'Valor predeterminado o mensaje de error');
           $templateProcessor->setValue('puesto_actual.gerenciaMayuscula', 'Valor predeterminado o mensaje de error');
       }

       $templateProcessor->setValue('puesto_nuevo.item', $incorporacion->puesto_nuevo->item);
       $templateProcessor->setValue('puesto_nuevo.denominacionMayuscula', mb_strtoupper($incorporacion->puesto_nuevo->denominacion, 'UTF-8'));

       $nombreDepartamento = mb_strtoupper($incorporacion->puesto_nuevo->departamento->nombre, 'UTF-8');
       $inicialDepartamento = mb_strtoupper(substr($nombreDepartamento, 0, 1), 'UTF-8');
       if (in_array($inicialDepartamento, ['D'])) {
           $valorDepartamento = 'DEL ' . $nombreDepartamento;
       } elseif (in_array($inicialDepartamento, ['G', 'A', 'U', 'P'])) {
           $valorDepartamento = 'DE LA ' . $nombreDepartamento;
       } else {
           $valorDepartamento = 'DE ' . $nombreDepartamento;
       }
       $templateProcessor->setValue('puesto_nuevo.departamentoMayuscula', $valorDepartamento);

       $templateProcessor->setValue('puesto_nuevo.gerenciaMayuscula', mb_strtoupper($incorporacion->puesto_nuevo->departamento->gerencia->nombre, 'UTF-8'));

       $carbonFechaInfo = Carbon::parse($incorporacion->fecha_informe);
       setlocale(LC_TIME, 'es_UY');
       $carbonFechaInfo->locale('es_UY');
       $fechaInfoFormateada = $carbonFechaInfo->isoFormat('LL');
       $templateProcessor->setValue('incorporacion.fechaInfo', $fechaInfoFormateada);

       $templateProcessor->setValue('incorporacion.hp', $incorporacion->hp);
       $templateProcessor->setValue('incorporacion.citeInfNotaMinuta', $incorporacion->cite_nota_minuta);

       $carbonFechaNotaMinuta = Carbon::parse($incorporacion->fecha_nota_minuta);
       setlocale(LC_TIME, 'es_UY');
       $carbonFechaNotaMinuta->locale('es_UY');
       $fechaNotaMinutaFormateada = $carbonFechaNotaMinuta->isoFormat('LL');
       $templateProcessor->setValue('incorporacion.fechaNotaMinuta', $fechaNotaMinutaFormateada);

       $carbonFechaRecepcion = Carbon::parse($incorporacion->fecha_recepcion);
       setlocale(LC_TIME, 'es_UY');
       $carbonFechaRecepcion->locale('es_UY');
       $fechaRecepcionFormateada = $carbonFechaRecepcion->isoFormat('LL');
       $templateProcessor->setValue('incorporacion.fechaRecepcion', $fechaRecepcionFormateada);

       $templateProcessor->setValue('persona.nombreCompleto', $incorporacion->persona->nombre_completo);
       if ($incorporacion->puesto_actual) {
           $denominacion = isset($incorporacion->puesto_actual->denominacion) ? $incorporacion->puesto_actual->denominacion : 'Valor predeterminado o mensaje de error';
           $templateProcessor->setValue('puesto_actual.denominacion', $denominacion);

           if ($incorporacion->puesto_actual->departamento && $incorporacion->puesto_actual->departamento->gerencia) {
               $templateProcessor->setValue('puesto_actual.gerencia', $incorporacion->puesto_actual->departamento->gerencia->nombre);
               $templateProcessor->setValue('puesto_actual.departamento', $incorporacion->puesto_actual->departamento->nombre);
           } else {
               $templateProcessor->setValue('puesto_actual.gerencia', 'Valor predeterminado o mensaje de error');
               $templateProcessor->setValue('puesto_actual.departamento', 'Valor predeterminado o mensaje de error');
           }

           $salario = isset($incorporacion->puesto_actual->salario) ? $incorporacion->puesto_actual->salario : 'Valor predeterminado o mensaje de error';
           $templateProcessor->setValue('puesto_actual.salario', $salario);
       } else {
           $templateProcessor->setValue('puesto_actual.denominacion', 'Valor predeterminado o mensaje de error');
           $templateProcessor->setValue('puesto_actual.gerencia', 'Valor predeterminado o mensaje de error');
           $templateProcessor->setValue('puesto_actual.departamento', 'Valor predeterminado o mensaje de error');
           $templateProcessor->setValue('puesto_actual.salario', 'Valor predeterminado o mensaje de error');
       }

       $templateProcessor->setValue('puesto_nuevo.denominacion', $incorporacion->puesto_nuevo->denominacion);

       $templateProcessor->setValue('puesto_nuevo.gerencia', $incorporacion->puesto_nuevo->departamento->gerencia->nombre);
       $templateProcessor->setValue('puesto_nuevo.departamento', $incorporacion->puesto_nuevo->departamento->nombre);
       $templateProcessor->setValue('puesto_nuevo.salario', $incorporacion->puesto_nuevo->salario);
       $templateProcessor->setValue('puesto_nuevo.salarioLiteral', $incorporacion->puesto_nuevo->salario_literal);
       $templateProcessor->setValue('puesto_nuevo.estado', $incorporacion->puesto_nuevo->estado);
       $templateProcessor->setValue('persona.formacion', $incorporacion->persona->formacion);
       $templateProcessor->setValue('persona.grado', $incorporacion->persona->grado_academico->nombre ?? 'Valor predeterminado');
       $templateProcessor->setValue('persona.areaformacion', $incorporacion->persona->area_formacion->nombre ?? 'Valor predeterminado');
      $templateProcessor->setValue('persona.institucion', $incorporacion->persona->institucion->nombre ?? 'Valor predeterminado');

       $carbonFechaConclusion = Carbon::parse($incorporacion->persona->anio_conclusion);
       setlocale(LC_TIME, 'es_UY');
       $carbonFechaConclusion->locale('es_UY');
       $fechaConclusionFormateada = $carbonFechaConclusion->isoFormat('LL');
       $templateProcessor->setValue('persona.conclusion', $fechaConclusionFormateada);

       $templateProcessor->setValue('incorporacion.respaldoFormacion', $this->obtenerTextoSegunValorDeFormacion($incorporacion->respaldo_formacion));

       if ($incorporacion) {
           $puestoNuevo = $incorporacion->puesto_nuevo;
           if ($puestoNuevo) {
               $requisitosPuestoNuevo = $puestoNuevo->requisitos;
               if ($requisitosPuestoNuevo->isNotEmpty()) {
                   $primerRequisitoPuestoNuevo = $requisitosPuestoNuevo->first();
                   if ($primerRequisitoPuestoNuevo) {
                       $formacionRequerida = $primerRequisitoPuestoNuevo->formacion_requerida;
                       $expProfesionalSegunCargo = $primerRequisitoPuestoNuevo->experiencia_profesional_segun_cargo;
                       $expRelacionadoAlArea = $primerRequisitoPuestoNuevo->experiencia_relacionado_al_area;
                       $expEnFuncionesDeMando = $primerRequisitoPuestoNuevo->experiencia_en_funciones_de_mando;

                       $templateProcessor->setValue('puesto_nuevo.formacion', $formacionRequerida);
                       $templateProcessor->setValue('puesto_nuevo.expSegunCargo', $expProfesionalSegunCargo);
                       $templateProcessor->setValue('puesto_nuevo.expSegunArea', $expRelacionadoAlArea);
                       $templateProcessor->setValue('puesto_nuevo.expEnMando', $expEnFuncionesDeMando);
                   }
               }
           }
       }

       $templateProcessor->setValue('puesto_nuevo.cumpleExpSegunCargo', $this->obtenerTextoSegunValor($incorporacion->cumple_exp_profesional));
       $templateProcessor->setValue('puesto_nuevo.cumpleExpSegunArea', $this->obtenerTextoSegunValor($incorporacion->cumple_exp_especifica));
       $templateProcessor->setValue('puesto_nuevo.cumpleExpEnMando', $this->obtenerTextoSegunValor($incorporacion->cumple_exp_mando));
       $templateProcessor->setValue('puesto_nuevo.cumpleFormacion', $this->obtenerTextoSegunValorDeFormacion($incorporacion->cumple_con_formacion));
       $templateProcessor->setValue('puesto_nuevo.salario_literal', $incorporacion->puesto_nuevo->salario_literal);

       $nombreDepartamento = $incorporacion->puesto_nuevo->departamento->nombre;
       $inicialDepartamento = substr($nombreDepartamento, 0, 1);
       if (in_array($inicialDepartamento, ['D'])) {
           $valorDepartamento = 'del ' . $nombreDepartamento;
       } elseif (in_array($inicialDepartamento, ['G', 'A', 'U', 'P'])) {
           $valorDepartamento = 'de la ' . $nombreDepartamento;
       } else {
           $valorDepartamento = 'de ' . $nombreDepartamento;
       }
       $templateProcessor->setValue('puesto_nuevo.departamentoRef', $valorDepartamento);

       if (isset($incorporacion->puesto_actual)) {
           $fileName = 'InfNotaCambioItem_' . $incorporacion->persona->nombre_completo;
       } else {
           $fileName = 'InfNota_' . $incorporacion->persona->nombre_completo;
       }
       $savedPath = $disk->path('generados/') . $fileName . '.docx';
       $templateProcessor->saveAs($savedPath);

       return response()->json(['incorporacion' => $incorporacion, 'filePath' => $fileName . '.docx']);
   }

   //para informe con minuta
   public function genFormInformeMinuta($incorporacionId)
   {
       $incorporacion = Incorporacion::find($incorporacionId);

       if (!isset($incorporacion)) {
           return response('', 404);
       }

       $incorporacion->incorporacion_estado = 3;
       $incorporacion->save();

       $disk = Storage::disk('form_templates');
       if (isset($incorporacion->puesto_actual)) {
           $pathTemplate = $disk->path('InfMinutaCambioItem.docx'); // ruta de plantilla
       } else {
           $pathTemplate = $disk->path('informeminuta.docx');
       }
       $templateProcessor = new TemplateProcessor($pathTemplate);

       $templateProcessor->setValue('incorporacion.citeInforme', $incorporacion->cite_informe);
       $templateProcessor->setValue('incorporacion.codigoNotaMinuta', $incorporacion->codigo_nota_minuta);

       //falta el responsable y su profesion

       $nombreCompleto = $incorporacion->persona->nombre_completo;
       $sexo = $incorporacion->persona->sexo;
       if ($sexo === 'F') {
           $templateProcessor->setValue('persona.referenciaMayuscula', 'DE LA SERVIDORA PÚBLICA INTERINA ' . mb_strtoupper($nombreCompleto, 'UTF-8'));
           $templateProcessor->setValue('persona.referenciaMayuscula1', 'SERVIDORA PÚBLICA INTERINA DE LA SEÑORA ' . mb_strtoupper($nombreCompleto, 'UTF-8'));
           $templateProcessor->setValue('persona.referencia', 'de la servidora publica interina ' . $nombreCompleto);
           $templateProcessor->setValue('persona.referencia1', ' servidora publica interina de la señora ' . $nombreCompleto);
           $templateProcessor->setValue('persona.referenciaAlPrincipio', 'La servidora publica interina ' . $nombreCompleto);
           $templateProcessor->setValue('persona.referenciaAlPrincipio1', 'La señora ' . $nombreCompleto);
       } else {
           $templateProcessor->setValue('persona.referenciaMayuscula', 'DEL SERVIDOR PÚBLICO INTERINO ' . mb_strtoupper($nombreCompleto, 'UTF-8'));
           $templateProcessor->setValue('persona.referenciaMayuscula1', 'SERVIDOR PÚBLICO INTERINO DEL SEÑOR ' . mb_strtoupper($nombreCompleto, 'UTF-8'));
           $templateProcessor->setValue('persona.referencia', 'del servidor publico interino ' . $nombreCompleto);
           $templateProcessor->setValue('persona.referencia1', 'servidor publico interino del señor ' . $nombreCompleto);
           $templateProcessor->setValue('persona.referenciaAlPrincipio', 'El servidor publico interino ' . $nombreCompleto);
           $templateProcessor->setValue('persona.referenciaAlPrincipio1', 'El señor ' . $nombreCompleto);
       }

       if ($incorporacion && $incorporacion->puesto_actual) {
           $templateProcessor->setValue('puesto_actual.item', optional($incorporacion->puesto_actual)->item);
           $templateProcessor->setValue('puesto_actual.denominacionMayuscula', mb_strtoupper(optional($incorporacion->puesto_actual)->denominacion, 'UTF-8'));
       }


       if ($incorporacion && $incorporacion->puesto_actual && $incorporacion->puesto_actual->departamento) {
           $nombreDepartamento = mb_strtoupper(optional($incorporacion->puesto_actual->departamento)->nombre, 'UTF-8');
       } else {
           $nombreDepartamento = null;
       }

       $inicialDepartamento = mb_strtoupper(substr($nombreDepartamento, 0, 1), 'UTF-8');
       if (in_array($inicialDepartamento, ['D'])) {
           $valorDepartamento = 'DEL ' . $nombreDepartamento;
       } elseif (in_array($inicialDepartamento, ['G', 'A', 'U', 'P'])) {
           $valorDepartamento = 'DE LA ' . $nombreDepartamento;
       } else {
           $valorDepartamento = 'DE ' . $nombreDepartamento;
       }
       $templateProcessor->setValue('puesto_actual.departamentoMayuscula', $valorDepartamento);

       if ($incorporacion && $incorporacion->puesto_actual && $incorporacion->puesto_actual->departamento && $incorporacion->puesto_actual->departamento->gerencia) {
           $nombreGerencia = mb_strtoupper(optional($incorporacion->puesto_actual->departamento->gerencia)->nombre, 'UTF-8');
       } else {
           $nombreGerencia = null;
       }

       $templateProcessor->setValue('puesto_actual.gerenciaMayuscula', $nombreGerencia);

       $templateProcessor->setValue('puesto_nuevo.item', $incorporacion->puesto_nuevo->item);
       $templateProcessor->setValue('puesto_nuevo.denominacionMayuscula', mb_strtoupper($incorporacion->puesto_nuevo->denominacion, 'UTF-8'));

       $nombreDepartamento = mb_strtoupper($incorporacion->puesto_nuevo->departamento->nombre, 'UTF-8');
       $inicialDepartamento = mb_strtoupper(substr($nombreDepartamento, 0, 1), 'UTF-8');
       if (in_array($inicialDepartamento, ['D'])) {
           $valorDepartamento = 'DEL ' . $nombreDepartamento;
       } elseif (in_array($inicialDepartamento, ['G', 'A', 'U', 'P'])) {
           $valorDepartamento = 'DE LA ' . $nombreDepartamento;
       } else {
           $valorDepartamento = 'DE ' . $nombreDepartamento;
       }
       $templateProcessor->setValue('puesto_nuevo.departamentoMayuscula', $valorDepartamento);

       $templateProcessor->setValue('puesto_nuevo.gerenciaMayuscula', mb_strtoupper($incorporacion->puesto_nuevo->departamento->gerencia->nombre, 'UTF-8'));

       $carbonFechaInfo = Carbon::parse($incorporacion->fecha_informe);
       setlocale(LC_TIME, 'es_UY');
       $carbonFechaInfo->locale('es_UY');
       $fechaInfoFormateada = $carbonFechaInfo->isoFormat('LL');
       $templateProcessor->setValue('incorporacion.fechaInfo', $fechaInfoFormateada);

       $templateProcessor->setValue('incorporacion.hp', $incorporacion->hp);
       $templateProcessor->setValue('incorporacion.codigoNotaMinuta', $incorporacion->codigo_nota_minuta);
       $templateProcessor->setValue('incorporacion.citeInfNotaMinuta', $incorporacion->cite_nota_minuta);

       $carbonFechaNotaMinuta = Carbon::parse($incorporacion->fecha_nota_minuta);
       setlocale(LC_TIME, 'es_UY');
       $carbonFechaNotaMinuta->locale('es_UY');
       $fechaNotaMinutaFormateada = $carbonFechaNotaMinuta->isoFormat('LL');
       $templateProcessor->setValue('incorporacion.fechaNotaMinuta', $fechaNotaMinutaFormateada);

       $carbonFechaRecepcion = Carbon::parse($incorporacion->fecha_recepcion);
       setlocale(LC_TIME, 'es_UY');
       $carbonFechaRecepcion->locale('es_UY');
       $fechaRecepcionFormateada = $carbonFechaRecepcion->isoFormat('LL');
       $templateProcessor->setValue('incorporacion.fechaRecepcion', $fechaRecepcionFormateada);

       $templateProcessor->setValue('persona.nombreCompleto', $incorporacion->persona->nombre_completo);

       $templateProcessor->setValue('puesto_actual.denominacion', optional($incorporacion->puesto_actual)->denominacion);
       if ($incorporacion && $incorporacion->puesto_actual) {
           $puestoActual = $incorporacion->puesto_actual;

           if ($puestoActual->departamento) {
               $departamento = $puestoActual->departamento;

               if ($departamento->gerencia) {
                   $gerenciaNombre = $departamento->gerencia->nombre;
                   $templateProcessor->setValue('puesto_actual.gerencia', $gerenciaNombre);
               }
           }
           $templateProcessor->setValue('puesto_actual.departamento', optional($incorporacion->puesto_nuevo->departamento)->nombre);
           $templateProcessor->setValue('puesto_actual.salario', optional($puestoActual)->salario);
       }

       $templateProcessor->setValue('puesto_nuevo.departamento', $incorporacion->puesto_nuevo->departamento->nombre);
       $templateProcessor->setValue('puesto_nuevo.gerencia', $incorporacion->puesto_nuevo->departamento->gerencia->nombre);
       $templateProcessor->setValue('puesto_nuevo.salario', $incorporacion->puesto_nuevo->salario);
       $templateProcessor->setValue('puesto_nuevo.salarioLiteral', $incorporacion->puesto_nuevo->salario_literal);
       $templateProcessor->setValue('puesto_nuevo.estado', $incorporacion->puesto_nuevo->estado);
       $templateProcessor->setValue('persona.formacion', $incorporacion->persona->formacion);
       $templateProcessor->setValue('persona.grado', $incorporacion->persona->grado_academico->nombre ?? 'Valor predeterminado');
       $templateProcessor->setValue('persona.areaformacion', $incorporacion->persona->area_formacion->nombre ?? 'Valor predeterminado');
       $templateProcessor->setValue('persona.institucion', $incorporacion->persona->institucion->nombre ?? 'Valor predeterminado');

       $carbonFechaConclusion = Carbon::parse($incorporacion->persona->anio_conclusion);
       setlocale(LC_TIME, 'es_UY');
       $carbonFechaConclusion->locale('es_UY');
       $fechaConclusionFormateada = $carbonFechaConclusion->isoFormat('LL');
       $templateProcessor->setValue('persona.conclusion', $fechaConclusionFormateada);

       $templateProcessor->setValue('incorporacion.respaldoFormacion', $this->obtenerTextoSegunValorDeFormacion($incorporacion->respaldo_formacion));

       if ($incorporacion) {
           $puestoNuevo = $incorporacion->puesto_nuevo;
           if ($puestoNuevo) {
               $requisitosPuestoNuevo = $puestoNuevo->requisitos;
               if ($requisitosPuestoNuevo->isNotEmpty()) {
                   $primerRequisitoPuestoNuevo = $requisitosPuestoNuevo->first();
                   if ($primerRequisitoPuestoNuevo) {
                       $formacionRequerida = $primerRequisitoPuestoNuevo->formacion_requerida;
                       $expProfesionalSegunCargo = $primerRequisitoPuestoNuevo->experiencia_profesional_segun_cargo;
                       $expRelacionadoAlArea = $primerRequisitoPuestoNuevo->experiencia_relacionado_al_area;
                       $expEnFuncionesDeMando = $primerRequisitoPuestoNuevo->experiencia_en_funciones_de_mando;

                       $templateProcessor->setValue('puesto_nuevo.formacion', $formacionRequerida);
                       $templateProcessor->setValue('puesto_nuevo.expSegunCargo', $expProfesionalSegunCargo);
                       $templateProcessor->setValue('puesto_nuevo.expSegunArea', $expRelacionadoAlArea);
                       $templateProcessor->setValue('puesto_nuevo.expEnMando', $expEnFuncionesDeMando);
                   }
               }
           }
       }

       $templateProcessor->setValue('puesto_nuevo.cumpleExpSegunCargo', $this->obtenerTextoSegunValor($incorporacion->cumple_exp_profesional));
       $templateProcessor->setValue('puesto_nuevo.cumpleExpSegunArea', $this->obtenerTextoSegunValor($incorporacion->cumple_exp_especifica));
       $templateProcessor->setValue('puesto_nuevo.cumpleExpEnMando', $this->obtenerTextoSegunValor($incorporacion->cumple_exp_mando));
       $templateProcessor->setValue('puesto_nuevo.cumpleFormacion', $this->obtenerTextoSegunValorDeFormacion($incorporacion->cumple_con_formacion));
       $templateProcessor->setValue('persona.profesion', $incorporacion->persona->profesion);
       $templateProcessor->setValue('puesto_nuevo.salario_literal', $incorporacion->puesto_nuevo->salario_literal);

       $templateProcessor->setValue('puesto_nuevo.denominacion', $incorporacion->puesto_nuevo->denominacion);

       $nombreDepartamento = $incorporacion->puesto_nuevo->departamento->nombre;
       $inicialDepartamento = substr($nombreDepartamento, 0, 1);
       if (in_array($inicialDepartamento, ['D'])) {
           $valorDepartamento = 'del ' . $nombreDepartamento;
       } elseif (in_array($inicialDepartamento, ['G', 'A', 'U', 'P'])) {
           $valorDepartamento = 'de la ' . $nombreDepartamento;
       } else {
           $valorDepartamento = 'de ' . $nombreDepartamento;
       }
       $templateProcessor->setValue('puesto_nuevo.departamentoRef', $valorDepartamento);

       if (isset($incorporacion->puesto_actual)) {
           $fileName = 'InfMinutaCambioItem_' . $incorporacion->persona->nombre_completo;
       } else {
           $fileName = 'informeminuta.docx_' . $incorporacion->persona->nombre_completo;
       }
       $savedPath = $disk->path('generados/') . $fileName . '.docx';
       $templateProcessor->saveAs($savedPath);

       return response()->json(['incorporacion' => $incorporacion, 'filePath' => $fileName . '.docx']);
   }

  //Informe RAP
  public function genFormRAP($incorporacionId)
  {
      $incorporacion = Incorporacion::find($incorporacionId);

      if (!isset($incorporacion)) {
          return response('', 404);
      }

      $incorporacion->incorporacion_estado = 3;
      $incorporacion->save();

      $disk = Storage::disk('form_templates');
      if (isset($incorporacion->puesto_actual)) {
          $pathTemplate = $disk->path('RAPCambioItem.docx');
      } else {
          $pathTemplate = $disk->path('RAP.docm');
      }
      $templateProcessor = new TemplateProcessor($pathTemplate);

      $templateProcessor->setValue('incorporacion.citeRAP', $incorporacion->cite_rap);
      $templateProcessor->setValue('incorporacion.codigoRAP', $incorporacion->codigo_rap);
      $templateProcessor->setValue('codigo', $incorporacion->codigo_rap);

      $carbonFechaRap = Carbon::parse($incorporacion->fecha_rap);
      setlocale(LC_TIME, 'es_UY');
      $carbonFechaRap->locale('es_UY');
      $fechaRapFormateada = $carbonFechaRap->isoFormat('LL');
      $templateProcessor->setValue('incorporacion.fechaRAP', $fechaRapFormateada);

      $templateProcessor->setValue('incorporacion.citeInforme', $incorporacion->cite_informe);

      $carbonFechaInforme = Carbon::parse($incorporacion->fecha_informe);
      setlocale(LC_TIME, 'es_UY');
      $carbonFechaInforme->locale('es_UY');
      $fechaInformeFormateada = $carbonFechaInforme->isoFormat('LL');
      $templateProcessor->setValue('incorporacion.fechaInforme', $fechaInformeFormateada);

      if (isset($incorporacion->puesto_actual)) {
          $descripcion = 'recomienda el cambio del Ítem N°' . $incorporacion->puesto_actual->item . ', al Ítem N°' . $incorporacion->puesto_nuevo->item;

      } else {
          $descripcion = 'recomienda la designación al Ítem N°' . $incorporacion->puesto_nuevo->item;
      }
      $templateProcessor->setValue('descripcion', $descripcion);

      $nombreCompleto = $incorporacion->persona->nombre_completo;
      $sexo = $incorporacion->persona->sexo;

      if ($sexo === 'F') {
          $templateProcessor->setValue('persona.deLa', 'de la servidora publica ' . $nombreCompleto);
          $templateProcessor->setValue('persona.reasignada', 'a la servidora publica interina ' . $nombreCompleto);
      } else {
          $templateProcessor->setValue('persona.deLa', 'del servidor publico ' . $nombreCompleto);
          $templateProcessor->setValue('persona.reasignada', 'al servidor publico interino ' . $nombreCompleto);
      }

      $templateProcessor->setValue('persona.ci', $incorporacion->persona->ci);
      $templateProcessor->setValue('persona.exp', $incorporacion->persona->exp);
      $templateProcessor->setValue('puesto_nuevo.denominacion', $incorporacion->puesto_nuevo->denominacion);

      $nombreDepartamento = $incorporacion->puesto_nuevo->departamento->nombre;
      $inicialDepartamento = strtoupper(substr($nombreDepartamento, 0, 1));
      if (in_array($inicialDepartamento, ['D'])) {
          $valorDepartamento = 'del ' . $nombreDepartamento;
      } elseif (in_array($inicialDepartamento, ['G', 'A', 'U', 'P'])) {
          $valorDepartamento = 'de la ' . $nombreDepartamento;
      } else {
          $valorDepartamento = 'de ' . $nombreDepartamento;
      }
      $templateProcessor->setValue('puesto_nuevo.departamento', $valorDepartamento . ' ');

      $valorGerencia = $incorporacion->puesto_nuevo->departamento->gerencia->nombre;
      $templateProcessor->setValue('puesto_nuevo.gerencia', $valorGerencia . ' ');

      $templateProcessor->setValue('puesto_nuevo.item', $incorporacion->puesto_nuevo->item);
      $templateProcessor->setValue('puesto_nuevo.salario', $incorporacion->puesto_nuevo->salario);
      $templateProcessor->setValue('puesto_nuevo.salarioLiteral', $incorporacion->puesto_nuevo->salario_literal);

      $carbonFechaIncorporacion = Carbon::parse($incorporacion->fecha_de_incorporacion);
      setlocale(LC_TIME, 'es_UY');
      $carbonFechaIncorporacion->locale('es_UY');
      $fechaIncorporacionFormateada = $carbonFechaIncorporacion->isoFormat('LL');
      $templateProcessor->setValue('incorporacion.fechaDeIncorporacion', $fechaIncorporacionFormateada);

      $templateProcessor->setValue('incorporacion.hp', $incorporacion->hp);

      if (isset($incorporacion->puesto_actual)) {
          $fileName = 'RAPCambioItem_' . $incorporacion->persona->nombre_completo;
      } else {
          $fileName = 'RAP_' . $incorporacion->persona->nombre_completo;
      }
      $savedPath = $disk->path('generados/') . $fileName . '.docx';
      $templateProcessor->saveAs($savedPath);

      return response()->json(['incorporacion' => $incorporacion, 'filePath' => $fileName . '.docx']);
  }

  //PARA MEMORANDUM
  public function genFormMemo($incorporacionId)
  {
      $incorporacion = Incorporacion::find($incorporacionId);

      if (!isset($incorporacion)) {
          return response('', 404);
      }

      $incorporacion->incorporacion_estado = 3;
      $incorporacion->save();

      $disk = Storage::disk('form_templates');
      if (isset($incorporacion->puesto_actual)) {
          $pathTemplate = $disk->path('MemoCambioItem.docx');
      } else {
          $pathTemplate = $disk->path('memorandum.docx');
      }

      $templateProcessor = new TemplateProcessor($pathTemplate);

      $templateProcessor->setValue('incorporacion.codigoMemorandum', $incorporacion->codigo_memorandum);
      $templateProcessor->setValue('incorporacion.citeMemorandum', $incorporacion->cite_memorandum);

      $carbonFechaMemo = Carbon::parse($incorporacion->fecha_memorandum);
      setlocale(LC_TIME, 'es_UY');
      $carbonFechaMemo->locale('es_UY');
      $fechaMemoFormateada = $carbonFechaMemo->isoFormat('LL');
      $templateProcessor->setValue('fechaMemo', $fechaMemoFormateada);

      $templateProcessor->setValue('persona.nombreCompleto', $incorporacion->persona->nombre_completo);

      if (isset($incorporacion->puesto_actual)) {
          $denominacion = $incorporacion->puesto_actual->denominacion;
      } else {
          $denominacion = $incorporacion->puesto_nuevo->denominacion;
      }
      $denominacionEnMayusculas = mb_strtoupper($denominacion, 'UTF-8');
      $templateProcessor->setValue('denominacionPuesto', $denominacionEnMayusculas);

      $primerApellido = $incorporacion->persona->primer_apellido;
      $sexo = $incorporacion->persona->sexo;

      if ($sexo === 'F') {
          $templateProcessor->setValue('persona.para', 'Señora ' . $primerApellido);
          $templateProcessor->setValue('persona.reasignada', 'reasignada' . ' ');
      } else {
          $templateProcessor->setValue('persona.para', 'Señor ' . $primerApellido);
          $templateProcessor->setValue('persona.reasignada', 'reasignado' . ' ');

      }

      $templateProcessor->setValue('incoporacion.codigoRap', $incorporacion->codigo_rap);
      $templateProcessor->setValue('puesto_nuevo.denominacion', $incorporacion->puesto_nuevo->denominacion);

      $nombreDepartamento = $incorporacion->puesto_nuevo->departamento->nombre;
      $inicialDepartamento = strtoupper(substr($nombreDepartamento, 0, 1));
      if (in_array($inicialDepartamento, ['D'])) {
          $valorDepartamento = 'del ' . $nombreDepartamento;
      } elseif (in_array($inicialDepartamento, ['G', 'A', 'U', 'P'])) {
          $valorDepartamento = 'de la ' . $nombreDepartamento;
      } else {
          $valorDepartamento = 'de ' . $nombreDepartamento;
      }
      $templateProcessor->setValue('puesto_nuevo.departamento', $valorDepartamento);

      $templateProcessor->setValue('puesto_nuevo.gerencia', $incorporacion->puesto_nuevo->departamento->gerencia->nombre);
      $templateProcessor->setValue('puesto_nuevo.item', $incorporacion->puesto_nuevo->item);
      $templateProcessor->setValue('puesto_nuevo.salario', $incorporacion->puesto_nuevo->salario);
      $templateProcessor->setValue('puesto_nuevo.salarioLiteral', $incorporacion->puesto_nuevo->salario_literal);

      $carbonFechaIncorporacion = Carbon::parse($incorporacion->fecha_de_incorporacion);
      setlocale(LC_TIME, 'es_UY');
      $carbonFechaIncorporacion->locale('es_UY');
      $fechaIncorporacionFormateada = $carbonFechaIncorporacion->isoFormat('LL');
      $templateProcessor->setValue('incorporacion.fechaDeIncorporacion', $fechaIncorporacionFormateada);

      $templateProcessor->setValue('incorporacion.hp', $incorporacion->hp);

      if (isset($incorporacion->puesto_actual)) {
          $fileName = 'MemoCambioItem_' . $incorporacion->persona->nombre_completo;
      } else {
          $fileName = 'Memorandum_' . $incorporacion->persona->nombre_completo;
      }
      $savedPath = $disk->path('generados/') . $fileName . '.docx';
      $templateProcessor->saveAs($savedPath);

      return response()->json(['incorporacion' => $incorporacion, 'filePath' => $fileName . '.docx']);
  }

   //para acta de posesion de cambio de item
   public function genFormActaDePosesion($incorporacionId)
   {
       $incorporacion = Incorporacion::find($incorporacionId);

       if (!isset($incorporacion)) {
           return response('', 404);
       }

       $incorporacion->incorporacion_estado = 3;
       $incorporacion->save();

       $disk = Storage::disk('form_templates');
       $pathTemplate = $disk->path('ActaDePosesionCambioDeItem.docx');
       if (isset($incorporacion->puesto_actual)) {
           $pathTemplate = $disk->path('ActaDePosesionCambioDeItem.docx');
       } else {
           $pathTemplate = $disk->path('R-0242-01.docx');
       }
       $templateProcessor = new TemplateProcessor($pathTemplate);

       $nombreGerencia = $incorporacion->puesto_nuevo->departamento->gerencia->nombre;
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

       $carbonFechaIncorporacion = Carbon::parse($incorporacion->fecha_de_incorporacion);
       setlocale(LC_TIME, 'es_UY');
       $carbonFechaIncorporacion->locale('es_UY');
       $fechaIncorporacionFormateada = $carbonFechaIncorporacion->isoFormat('LL');
       $nombreDiaIncorporacion = $carbonFechaIncorporacion->isoFormat('dddd');
       $templateProcessor->setValue('incorporacion.nombreDiaDeIncorporacion', $nombreDiaIncorporacion);

       $carbonFechaIncorporacion = Carbon::parse($incorporacion->fecha_de_incorporacion);
       setlocale(LC_TIME, 'es_UY');
       $carbonFechaIncorporacion->locale('es_UY');
       $fechaIncorporacionFormateada = $carbonFechaIncorporacion->isoFormat('LL');
       $templateProcessor->setValue('incorporacion.fechaDeIncorporacion', $fechaIncorporacionFormateada);

       $sexo = $incorporacion->persona->sexo;

       if ($sexo === 'F') {
           $templateProcessor->setValue('ciudadano', 'la ciudadana');
       } else {
           $templateProcessor->setValue('ciudadano', 'el ciudadano');

       }

       $templateProcessor->setValue('persona.nombreCompleto', $incorporacion->persona->nombre_completo);
       $templateProcessor->setValue('persona.ci', $incorporacion->persona->ci);
       $templateProcessor->setValue('persona.exp', $incorporacion->persona->exp);
       $templateProcessor->setValue('incorporacion.codigoRAP', $incorporacion->codigo_rap);
       $templateProcessor->setValue('puesto_nuevo.denominacion', $incorporacion->puesto_nuevo->denominacion);

       $nombreDepartamento = $incorporacion->puesto_nuevo->departamento->nombre;
       $inicialDepartamento = strtoupper(substr($nombreDepartamento, 0, 1));
       if (in_array($inicialDepartamento, ['D'])) {
           $valorDepartamento = 'del ' . $nombreDepartamento;
       } elseif (in_array($inicialDepartamento, ['G', 'A', 'U', 'P'])) {
           $valorDepartamento = 'de la ' . $nombreDepartamento;
       } else {
           $valorDepartamento = 'de ' . $nombreDepartamento;
       }
       $templateProcessor->setValue('puesto_nuevo.departamento', $valorDepartamento);

       $templateProcessor->setValue('puesto_nuevo.gerencia', $incorporacion->puesto_nuevo->departamento->gerencia->nombre);
       $templateProcessor->setValue('puesto_nuevo.item', $incorporacion->puesto_nuevo->item);

       if (isset($incorporacion->puesto_actual)) {
           $fileName = 'ActaDePosesionCambioDeItem_' . $incorporacion->persona->nombre_completo;
       } else {
           $fileName = 'ActaDePosesion_' . $incorporacion->persona->nombre_completo;
       }
       $savedPath = $disk->path('generados/') . $fileName . '.docx';
       $templateProcessor->saveAs($savedPath);

       return response()->json(['incorporacion' => $incorporacion, 'filePath' => $fileName . '.docx']);
   }

   //para acta de entrega
   public function genFormActaDeEntrega($incorporacionId)
   {
       $incorporacion = Incorporacion::find($incorporacionId);

       if (!isset($incorporacion)) {
           return response('', 404);
       }

       $incorporacion->incorporacion_estado = 3;
       $incorporacion->save();

       $disk = Storage::disk('form_templates');
       if (isset($incorporacion->puesto_actual)) {
           $pathTemplate = $disk->path('ActaEntregaCambioDeItem.docx');
       } else {
           $pathTemplate = $disk->path('R-0243-01.docx');
       }
       $templateProcessor = new TemplateProcessor($pathTemplate);

       $nombreGerencia = $incorporacion->puesto_nuevo->departamento->gerencia->nombre;
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

       $carbonFechaIncorporacion = Carbon::parse($incorporacion->fecha_de_incorporacion);
       setlocale(LC_TIME, 'es_UY');
       $carbonFechaIncorporacion->locale('es_UY');
       $fechaIncorporacionFormateada = $carbonFechaIncorporacion->isoFormat('LL');
       $templateProcessor->setValue('fechaIncorporacion', $fechaIncorporacionFormateada);

       $templateProcessor->setValue('persona.nombreCompleto', $incorporacion->persona->nombre_completo);
       $templateProcessor->setValue('puesto_nuevo.denominacion', $incorporacion->puesto_nuevo->denominacion);
       $templateProcessor->setValue('puesto_nuevo.departamento', $incorporacion->puesto_nuevo->departamento->nombre);
       $templateProcessor->setValue('puesto_nuevo.gerencia', $incorporacion->puesto_nuevo->departamento->gerencia->nombre);

       if (isset($incorporacion->puesto_actual)) {
           $fileName = 'ActaEntregaCambioDeItem_' . $incorporacion->persona->nombre_completo;
       } else {
           $fileName = 'ActaEntrega_' . $incorporacion->persona->nombre_completo;
       }
       $savedPath = $disk->path('generados/') . $fileName . '.docx';
       $templateProcessor->saveAs($savedPath);

       return response()->json(['incorporacion' => $incorporacion, 'filePath' => $fileName . '.docx']);
   }
  
   //para informe R-0976 compromiso
   public function genFormCompromiso($incorporacionId)
   {
       $incorporacion = Incorporacion::find($incorporacionId);
       if (!isset($incorporacion)) {
           return response('', 404);
       }

       $disk = Storage::disk('form_templates');
       $pathTemplate = $disk->path('R-0976-01.docx'); // ruta de plantilla
       $templateProcessor = new TemplateProcessor($pathTemplate);

       $templateProcessor->setValue('persona.nombreCompleto', $incorporacion->persona->nombre_completo);
       $templateProcessor->setValue('persona.ci', $incorporacion->persona->ci);
       $templateProcessor->setValue('persona.exp', $incorporacion->persona->exp);

       $nombreGerencia = $incorporacion->puesto_nuevo->departamento->gerencia->nombre;
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

       Carbon::setLocale('es');
       $fechaHoy = Carbon::now();
       $fechaFormateada = $fechaHoy->isoFormat('LL');
       $templateProcessor->setValue('fecha', $fechaFormateada);

       $fileName = 'R-0976-01_' . $incorporacion->persona->nombre_completo;
       $savedPath = $disk->path('generados/') . $fileName . '.docx';
       $templateProcessor->saveAs($savedPath);
       return response()->json(['incorporacion' => $incorporacion, 'filePath' => $fileName . '.docx']);
   }

   //para informe R-0921 incompatibilidad
   public function genFormDeclaracionIncompatibilidad($incorporacionId)
   {
       $incorporacion = Incorporacion::find($incorporacionId);

       if (!isset($incorporacion)) {
           return response('', 404);
       }

       $incorporacion->incorporacion_estado = 3;
       $incorporacion->save();

       $disk = Storage::disk('form_templates');
       $pathTemplate = $disk->path('R-0921-01.docx'); // ruta de plantilla
       $templateProcessor = new TemplateProcessor($pathTemplate);
       $templateProcessor->setValue('persona.nombreCompleto', $incorporacion->persona->nombre_completo);
       $templateProcessor->setValue('persona.ci', $incorporacion->persona->ci);
       $templateProcessor->setValue('persona.exp', $incorporacion->persona->exp);
       $templateProcessor->setValue('puesto_nuevo.item', $incorporacion->puesto_nuevo->item);
       $templateProcessor->setValue('puesto_nuevo.denominacion', $incorporacion->puesto_nuevo->denominacion);
       $nombreDepartamento = $incorporacion->puesto_nuevo->departamento->nombre;
       $inicialDepartamento = substr($nombreDepartamento, 0, 1);

       if (in_array($inicialDepartamento, ['D'])) {
           $valorDepartamento = 'del ' . $nombreDepartamento;
       } elseif (in_array($inicialDepartamento, ['G', 'A', 'U', 'P'])) {
           $valorDepartamento = 'de la ' . $nombreDepartamento;
       } else {
           $valorDepartamento = 'de ' . $nombreDepartamento;
       }

       $templateProcessor->setValue('puesto_nuevo.departamento', $valorDepartamento);

       $templateProcessor->setValue('puesto_nuevo.gerencia', $incorporacion->puesto_nuevo->departamento->gerencia->nombre);
       $nombreGerencia = $incorporacion->puesto_nuevo->departamento->gerencia->nombre;
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

       Carbon::setLocale('es');
       $fechaHoy = Carbon::now();
       $fechaFormateada = $fechaHoy->isoFormat('LL');
       $templateProcessor->setValue('fecha', $fechaFormateada);

       $fileName = 'R-0921-01_' . $incorporacion->persona->nombre_completo;
       $savedPath = $disk->path('generados/') . $fileName . '.docx';
       $templateProcessor->saveAs($savedPath);

       return response()->json(['incorporacion' => $incorporacion, 'filePath' => $fileName . '.docx']);
   }

   //para informe R-0716 etica
   public function genFormEtica($incorporacionId)
   {
       $incorporacion = Incorporacion::find($incorporacionId);

       if (!isset($incorporacion)) {
           return response('', 404);
       }

       $incorporacion->incorporacion_estado = 3;
       $incorporacion->save();

       $disk = Storage::disk('form_templates');
       $pathTemplate = $disk->path('R-0716-01.docx'); // ruta de plantilla

       $templateProcessor = new TemplateProcessor($pathTemplate);
       $templateProcessor->setValue('persona.nombreCompleto', $incorporacion->persona->nombre_completo);
       $templateProcessor->setValue('persona.ci', $incorporacion->persona->ci);
       $templateProcessor->setValue('persona.exp', $incorporacion->persona->exp);
       $templateProcessor->setValue('puesto_nuevo.item', $incorporacion->puesto_nuevo->item);
       $templateProcessor->setValue('puesto_nuevo.denominacion', $incorporacion->puesto_nuevo->denominacion);

       $nombreDepartamento = $incorporacion->puesto_nuevo->departamento->nombre;
       $inicialDepartamento = substr($nombreDepartamento, 0, 1);

       if (in_array($inicialDepartamento, ['D'])) {
           $valorDepartamento = 'del ' . $nombreDepartamento;
       } elseif (in_array($inicialDepartamento, ['G', 'A', 'U', 'P'])) {
           $valorDepartamento = 'de la ' . $nombreDepartamento;
       } else {
           $valorDepartamento = 'de ' . $nombreDepartamento;
       }

       $templateProcessor->setValue('puesto_nuevo.departamento', $valorDepartamento);

       $templateProcessor->setValue('puesto_nuevo.gerencia', $incorporacion->puesto_nuevo->departamento->gerencia->nombre);
       $nombreGerencia = $incorporacion->puesto_nuevo->departamento->gerencia->nombre;
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

       Carbon::setLocale('es');
       $fechaHoy = Carbon::now();
       $fechaFormateada = $fechaHoy->isoFormat('LL');
       $templateProcessor->setValue('fecha', $fechaFormateada);

       $fileName = 'R-0716-01_' . $incorporacion->persona->nombre_completo;
       $savedPath = $disk->path('generados/') . $fileName . '.docx';
       $templateProcessor->saveAs($savedPath);

       return response()->json(['incorporacion' => $incorporacion, 'filePath' => $fileName . '.docx']);
   }

    //Para el R-1469, Remision de documentos
    public function genFormRemisionDeDocumentos($incorporacionId)
    {
        $incorporacion = Incorporacion::find($incorporacionId);

        if (!isset($incorporacion)) {
            return response('', 404);
        }

        $incorporacion->incorporacion_estado = 3;
        $incorporacion->save();

        $disk = Storage::disk('form_templates');
        $pathTemplate = $disk->path('R-1469-01-CambioItem.docx');
        $templateProcessor = new TemplateProcessor($pathTemplate);

        $templateProcessor->setValue('puesto_nuevo.gerencia', strtoupper($incorporacion->puesto_nuevo->departamento->gerencia->nombre));
        $templateProcessor->setValue('incoporacion.hp', strtoupper($incorporacion->hp));

        mb_internal_encoding("UTF-8");
        $templateProcessor->setValue('puesto_nuevo.departamento', mb_strtoupper($incorporacion->puesto_nuevo->departamento->nombre, "UTF-8"));

        $templateProcessor->setValue('persona.nombreCompleto', strtoupper($incorporacion->persona->nombre_completo));

        $templateProcessor->setValue('fechaMemo', $incorporacion->fecha_memorandum);
        $templateProcessor->setValue('incorporacion.fechaRAP', $incorporacion->fecha_rap);
        $templateProcessor->setValue('incorporacion.fechaDeIncorporacion', $incorporacion->fecha_de_incorporacion);

        if (isset($incorporacion->puesto_actual)) {
            $fileName = 'R-1469-01-CambioItem_' . $incorporacion->persona->nombre_completo;
        } else {
            $fileName = 'R-1469-01_' . $incorporacion->persona->nombre_completo;
        }
        $savedPath = $disk->path('generados/') . $fileName . '.docx';
        $templateProcessor->saveAs($savedPath);

        return response()->json(['incorporacion' => $incorporacion, 'filePath' => $fileName . '.docx']);
    }
    
   //para informe R-SGC-0033 confidencialidad
   public function genFormConfidencialidad($incorporacionId)
   {
       $incorporacion = Incorporacion::find($incorporacionId);

       if (!isset($incorporacion)) {
           return response('', 404);
       }

       $incorporacion->incorporacion_estado = 3;
       $incorporacion->save();

       $disk = Storage::disk('form_templates');
       $pathTemplate = $disk->path('R-SGC-0033-01.docx'); // ruta de plantilla

       $templateProcessor = new TemplateProcessor($pathTemplate);
       $templateProcessor->setValue('persona.nombreCompleto', $incorporacion->persona->nombre_completo);
       $templateProcessor->setValue('persona.ci', $incorporacion->persona->ci);
       $templateProcessor->setValue('persona.exp', $incorporacion->persona->exp);
       $templateProcessor->setValue('puesto_nuevo.denominacion', $incorporacion->puesto_nuevo->denominacion);

       $nombreDepartamento = $incorporacion->puesto_nuevo->departamento->nombre;
       $inicialDepartamento = substr($nombreDepartamento, 0, 1);

       if (in_array($inicialDepartamento, ['D'])) {
           $valorDepartamento = 'del ' . $nombreDepartamento;
       } elseif (in_array($inicialDepartamento, ['G', 'A', 'U', 'P'])) {
           $valorDepartamento = 'de la ' . $nombreDepartamento;
       } else {
           $valorDepartamento = 'de ' . $nombreDepartamento;
       }

       $templateProcessor->setValue('puesto_nuevo.departamento', $valorDepartamento);

       $templateProcessor->setValue('puesto_nuevo.gerencia', $incorporacion->puesto_nuevo->departamento->gerencia->nombre);
       $nombreGerencia = $incorporacion->puesto_nuevo->departamento->gerencia->nombre;
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

       Carbon::setLocale('es');
       $fechaHoy = Carbon::now();
       $fechaFormateada = $fechaHoy->isoFormat('LL');
       $templateProcessor->setValue('fecha', $fechaFormateada);

       $fileName = 'R-SGC-0033-01_' . $incorporacion->persona->nombre_completo;
       $savedPath = $disk->path('generados/') . $fileName . '.docx';
       $templateProcessor->saveAs($savedPath);

       return response()->json(['incorporacion' => $incorporacion, 'filePath' => $fileName . '.docx']);
   }

  public function downloadEvalForm($fileName)
{
  $disk = Storage::disk('form_templates');
  return response()->download($disk->path('generados/') . $fileName)->deleteFileAfterSend(true);
}

  //funci_personaones de ayuda para ver si cumple o no cumple los requisit
  public function obtenerTextoSegunValor($valor)
  {
    switch ($valor) {
      case 0:
        return 'No';
      case 1:
        return 'Si';
      case 2:
        return 'No corresponde';
      default:
        return 'Valor no reconoci_personado';
    }
  }

  //funci_personaones de ayuda para ver si cumple o no cumple la formaci_personaon
  public function obtenerTextoSegunValorDeFormaci_personaon($valor)
  {
    switch ($valor) {
      case 0:
        return 'No Cumple';
      case 1:
        return 'Cumple';
      default:
        return 'Valor no reconoci_personado';
    }
  }
}

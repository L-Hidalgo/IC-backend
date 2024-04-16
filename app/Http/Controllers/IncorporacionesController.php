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
    return response()->json(['incorporacion' => $incorporacion, 'filePath' => $fileName . '.docx']);
  }
  
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
    $templateProcessor->setValue('persona.ci_persona', $incorporacion->persona->ci_persona);
    $templateProcessor->setValue('persona.exp_persona', $incorporacion->persona->exp_persona);

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
    $templateProcessor->setValue('fechaincorporacion', $fechaincorporacionFormateada);
    $fileName = 'R-1401_' . $incorporacion->persona->nombre_persona;
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

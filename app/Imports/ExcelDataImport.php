<?php

namespace App\Imports;

use App\Models\Gerencia;
use App\Models\Departamento;
use App\Models\Puesto;
use App\Models\Persona;
use App\Models\Funcionario;
use App\Models\Requisito;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ExcelDataImport implements ToModel, WithStartRow
{

    public function startRow(): int
    {
        return 6;
    }

    public function model(array $row)
    {
        $gerencia = $this->migrarGerencia($row[0], $row[2]);
        $departamento = $this->migrarDepartamento($row[3], $gerencia->id_gerencia);

        $persona = $this->migrarPersona($row[7], $row[9], $row[10], $row[11], $row[12], $row[15], $row[16], $row[17], $row[19]);

        $personaId = $persona ? $persona->id_persona : null;

        $puesto = $this->migrarPuesto($row[1], $row[4], $row[5], $row[6], $row[42], $departamento->id_departamento, $personaId);

        if ($puesto) {
            $funcionario = $this->migrarFuncionario(
                $row[20], // fecha inicio en el sin
                $row[21], // fecha inicio en el cargo
                $puesto->id_puesto,
                $personaId // Aquí está bien usar personaId
            );
            $this->migrarRequisito($row[43], $row[44], $row[45], $row[46], $puesto->id_puesto);
        }
    }

    public function migrarGerencia($abreviaturaGerencia, $nombreGerencia): Gerencia
    {
        $gerencia = Gerencia::where('nombre_gerencia', $nombreGerencia)->first();
        if (!isset($gerencia)) {
            $gerencia = Gerencia::create([
                'nombre_gerencia' => $nombreGerencia,
                'abreviatura_gerencia' => $abreviaturaGerencia
            ]);
        }
        return $gerencia;
    }

    public function migrarDepartamento($nombreDepartamento, $gerenciaId): Departamento
    {
        $departamento = Departamento::where('nombre_departamento', $nombreDepartamento)->where('gerencia_id', $gerenciaId)->first();
        if (!isset($departamento)) {
            $departamento = Departamento::create([
                'nombre_departamento' => $nombreDepartamento,
                'gerencia_id' => $gerenciaId
            ]);
        }
        return $departamento;
    }

    public function migrarPersona(
        $ci,  // 7
        $exp, // 9
        $primerApellido, // 10
        $segundoApellido, // 11
        $nombres,         // 12
        $profesion,       // 15
        $sexo,            // 16
        $fechaNacimiento, // 17
        $telefono         // 19
    ): ?Persona {
        if (empty($ci)) {
            return null;
        }

        $persona = Persona::where('ci_persona', $ci)->first();

        $timestamp = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($fechaNacimiento);
        $fechaNacimiento = Carbon::createFromTimestamp($timestamp)->format('Y-m-d');

        if ($persona) {
            $persona->update([
                'exp_persona' => $exp,
                'primer_apellido_persona' => $primerApellido,
                'segundo_apellido_persona' => $segundoApellido,
                'nombre_persona' => $nombres,
                'profesion_persona' => $profesion,
                'genero_persona' => $sexo,
                'fch_nacimiento_persona' => $fechaNacimiento,
                'telefono_persona' => $telefono,
            ]);
        } else {
            $persona = Persona::create([
                'ci_persona' => $ci,
                'exp_persona' => $exp,
                'primer_apellido_persona' => $primerApellido,
                'segundo_apellido_persona' => $segundoApellido,
                'nombre_persona' => $nombres,
                'profesion_persona' => $profesion,
                'genero_persona' => $sexo,
                'fch_nacimiento_persona' => $fechaNacimiento,
                'telefono_persona' => $telefono,
            ]);
        }
        return $persona;
    }

    public function migrarPuesto(
        $item,
        $denominacion,
        $salario,
        $salario_literal,
        $objetivo,
        $departamentoId,
        $personaId
    ): Puesto {
        $puesto = Puesto::where('item_puesto', $item)->first();

        $estadoId = $puesto && is_null($puesto->persona_actual_id) ? 1 : 2;

        if ($puesto) {
            Log::info('Puesto encontrado: ' . $item);
            $puesto->update([
                'denominacion_puesto' => $denominacion,
                'salario_puesto' => $salario,
                'salario_literal_puesto' => $salario_literal,
                'objetivo_puesto' => $objetivo,
                'departamento_id' => $departamentoId,
                'persona_actual_id' => $personaId ?? null,
                'estado_id' => $estadoId,
            ]);
        } else {
            $puesto = Puesto::create([
                'item_puesto' => $item,
                'denominacion_puesto' => $denominacion,
                'salario_puesto' => $salario,
                'salario_literal_puesto' => $salario_literal,
                'objetivo_puesto' => $objetivo,
                'departamento_id' => $departamentoId,
                'persona_actual_id' => $personaId ?? null,
                'estado_id' => $estadoId,
            ]);
        }
        return $puesto;
    }

    public function migrarFuncionario(
        $fchInicioSinFuncionario,
        $fchInicioPuestoFuncionario,
        $puestoId,
        $personaId
    ): ?Funcionario {
        $persona = Persona::find($personaId);
        $puesto = Puesto::find($puestoId);

        if (!$persona || !$puesto) {
            return null;
        }

        $codigoFileFuncionario = $puesto->item_puesto . '-' . $persona->ci_persona;

        $Funcionario = Funcionario::where('fch_inicio_sin_funcionario', $fchInicioSinFuncionario)
            ->where('puesto_id', $puestoId)
            ->where('persona_id', $personaId)
            ->first();

        if (!isset($Funcionario)) {
            $timestampfsin = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($fchInicioSinFuncionario);
            $fchInicioSinFuncionario = Carbon::createFromTimestamp($timestampfsin)->format('Y-m-d');

            $timestampFechaInicio = $this->convertirFechaATimestamp($fchInicioPuestoFuncionario);
            $fchInicioPuestoFuncionario = Carbon::createFromTimestamp($timestampFechaInicio)->format('Y-m-d');

            $Funcionario = Funcionario::create([
                'codigo_file_funcionario' => $codigoFileFuncionario,
                'fch_inicio_sin_funcionario' => $fchInicioSinFuncionario,
                'fch_inicio_puesto_funcionario' => $fchInicioPuestoFuncionario,
                'puesto_id' => $puesto->id_puesto,
                'persona_id' => $persona->id_persona,
            ]);
        }
        return $Funcionario;
    }


    public function migrarRequisito($formacionRequerida, $experienciaProfesionalSegunCargo, $experienciaRelacionadoAlArea, $experienciaEnFuncionesDeMando, $puestoId): Requisito
    {
        $requisitos = Requisito::where('puesto_id', $puestoId)->first();

        if ($requisitos) {
            $requisitos->update([
                'formacion_requisito' => $formacionRequerida,
                'exp_cargo_requisito' => $experienciaProfesionalSegunCargo,
                'exp_area_requisito' => $experienciaRelacionadoAlArea,
                'exp_mando_requisito' => $experienciaEnFuncionesDeMando
            ]);
        } else {
            $requisitos = Requisito::create([
                'puesto_id' => $puestoId,
                'formacion_requisito' => $formacionRequerida,
                'exp_cargo_requisito' => $experienciaProfesionalSegunCargo,
                'exp_area_requisito' => $experienciaRelacionadoAlArea,
                'exp_mando_requisito' => $experienciaEnFuncionesDeMando
            ]);
        }

        return $requisitos;
    }

    private function convertirFechaATimestamp($fecha)
    {
        try {
            $carbonDate = Carbon::createFromFormat('d/m/Y', $fecha);

            if ($carbonDate instanceof Carbon) {
                return $carbonDate->getTimestamp();
            }
        } catch (\Exception $e) {
            error_log("Error al convertir fecha: " . $e->getMessage());
        }

        try {
            $excelDate = intval($fecha);
            $carbonDate = Carbon::createFromTimestamp(($excelDate - 25569) * 86400);

            if ($carbonDate instanceof Carbon) {
                return $carbonDate->getTimestamp();
            }
        } catch (\Exception $e) {
            error_log("Error al convertir número de serie de Excel: " . $e->getMessage());
        }

        error_log("No se pudo convertir la fecha: $fecha");
        return 0;
    }
}

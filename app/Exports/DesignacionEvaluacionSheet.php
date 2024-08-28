<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DesignacionEvaluacionSheet implements FromArray, WithHeadings, WithStyles
{
    protected $incorporaciones;

    public function __construct($incorporaciones)
    {
        $this->incorporaciones = $incorporaciones;
    }

    public function array(): array
    {
        $data = $this->collectionDesignacion();
        return $data;
    }

    public function headings(): array
    {
        return [
            'NOMBRE',
            'EDAD',
            'FORMACIÓN ACADÉMICA',
            'EXPERIENCIA',
            'ITEM',
            'DENOMINACION DEL PUESTO',
            'GERENCIA',
            'DEPARTAMENTO',
            'SALARIO',
            'FORMACIÓN DEL ITEM',
            'EXP. PROFESIONAL  DEL ITEM',
            'EXP. RELACIONADA AL AREA DE FORMACIÓN  DEL ITEM',
            'EXP. EN FUNCIONES DE MANDO  DEL ITEM',
            'OBSERVACIÓN DE EVALUACIÓN',
            'DETALLE DE OBSERVACIÓN DE EVALUACIÓN',
            'FECHA DE OBSERVACIÓN DE EVALUACIÓN',
            'RESPONSABLE',
        ];
    }

    protected function collectionDesignacion()
    {
        return $this->incorporaciones->filter(function ($incorporacion) {
            return !is_null($incorporacion->puesto_nuevo_id) && is_null($incorporacion->puesto_actual_id);
        })->map(function ($incorporacion) {
            return $this->formatDataDesignacion($incorporacion);
        })->toArray();
    }

    protected function formatDataDesignacion($incorporacion)
    {
        $fechaNacimiento = Carbon::parse($incorporacion->persona->fch_nacimiento_persona);
        $edad = $fechaNacimiento->age;

        $detalle_observacion = "No se registró la observación de evaluación";

        if (!empty($incorporacion->obs_evaluacion_incorporacion)) {
            if ($incorporacion->obs_evaluacion_incorporacion == 'Cumple') {
                $detalle_observacion = "Si cumple";
            } elseif ($incorporacion->obs_evaluacion_incorporacion == 'No cumple') {
                if (empty($incorporacion->detalle_obs_evaluacion_incorporacion)) {
                    $detalle_observacion = "No se registró el detalle de observación de evaluación";
                } else {
                    $detalle_observacion = $incorporacion->detalle_obs_evaluacion_incorporacion;
                }
            }
        }

        $formacionAcademica = 'NO SE REGISTRO FORMACIÓN ACADÉMICA';
        if (!empty($incorporacion->persona->formacion) && !empty($incorporacion->persona->formacion[0]->gradoAcademico)) {
            $formacionAcademica = mb_strtoupper($incorporacion->persona->formacion[0]->gradoAcademico->nombre_grado_academico);
        }

        $datos = [
            'NOMBRE' => mb_strtoupper($incorporacion->persona->nombre_persona . ' ' . $incorporacion->persona->primer_apellido_persona . ' ' . $incorporacion->persona->segundo_apellido_persona),
            'EDAD' => $edad . ' AÑOS',
            'FORMACIÓN ACADÉMICA' => $formacionAcademica,
            'EXPERIENCIA' => $incorporacion->exp_evaluacion_incorporacion == 0 ? 'NO CUENTA CON EXPERIENCIA EN SERVICIO DE IMPUESTOS NACIONALES' : 'SI CUENTA CON EXPERIENCIA EN IMPUESTOS NACIONALES',
            'ITEM' => $incorporacion->puesto_nuevo->item_puesto,
            'DENOMINACION DEL PUESTO' => $incorporacion->puesto_nuevo->denominacion_puesto,
            'GERENCIA' => $incorporacion->puesto_nuevo->departamento->gerencia->nombre_gerencia,
            'DEPARTAMENTO' => $incorporacion->puesto_nuevo->departamento->nombre_departamento,
            'SALARIO' => $incorporacion->puesto_nuevo->salario_puesto,
            'FORMACIÓN DEL ITEM' => '',
            'EXP. PROFESIONAL  DEL ITEM' => '',
            'EXP. RELACIONADA AL AREA DE FORMACIÓN  DEL ITEM' => '',
            'EXP. EN FUNCIONES DE MANDO DEL ITEM' => '',
            'OBSERVACIÓN DE EVALUACIÓN' => empty($incorporacion->obs_evaluacion_incorporacion) ? 'NO SE REGISTRÓ EVALUACIÓN' : mb_strtoupper($incorporacion->obs_evaluacion_incorporacion),
            'DETALLE DE OBSERVACIÓN DE EVALUACIÓN' => empty($incorporacion->detalle_obs_evaluacion_incorporacion) ? 'NO SE REGISTRÓ EL DETALLE DE EVALUACIÓN' : mb_strtoupper($incorporacion->detalle_obs_evaluacion_incorporacion),
            'FECHA DE OBSERVACIÓN DE EVALUACIÓN' => empty($incorporacion->fch_obs_evaluacion_incorporacion) ? 'NO SE REGISTRÓ LA FCH. DE EVALUACIÓN' : $incorporacion->fch_obs_evaluacion_incorporacion,
            'RESPONSABLE' => $incorporacion->createdBy->name,
        ];

        foreach ($incorporacion->puesto_nuevo->requisitos as $requisito) {
            if ($requisito) {
                $datos['FORMACIÓN DEL ITEM'] = $requisito->formacion_requisito;
                $datos['EXP. PROFESIONAL  DEL ITEM'] = $requisito->exp_cargo_requisito;
                $datos['EXP. RELACIONADA AL AREA DE FORMACIÓN  DEL ITEM'] = $requisito->exp_area_requisito;
                $datos['EXP. EN FUNCIONES DE MANDO DEL ITEM'] = $requisito->exp_mando_requisito;
                break;
            }
        }

        return $datos;
    }


    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:Q1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '012e58']]
        ]);

        $sheet->setAutoFilter('A1:Q1');
        $sheet->setTitle('Designación');

        $lastRow = $sheet->getHighestRow();
        if ($lastRow >= 2) {
            $sheet->getStyle('I2:I' . $lastRow)->getNumberFormat()->setFormatCode('#,##0');
        }
        $this->adjustColumnWidths($sheet);
        return [];
    }

    protected function adjustColumnWidths(Worksheet $sheet)
    {
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        for ($col = 1; $col <= $highestColumnIndex; ++$col) {
            $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }
}

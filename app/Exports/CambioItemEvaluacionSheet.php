<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CambioItemEvaluacionSheet implements FromArray, WithHeadings, WithStyles
{
    protected $incorporaciones;

    public function __construct($incorporaciones)
    {
        $this->incorporaciones = $incorporaciones;
    }

    public function array(): array
    {
        $data = $this->collectionCambioItem();
        return $data;
    }

    public function headings(): array
    {
        return [
            'NOMBRE',
            'FORMACIÓN ACADÉMICA',
            'FECHA DE LA ULTIMA DESIGNACIÓN',
            'ITEM ACTUAL',
            'DENOMINACION DEL PUESTO ACTUAL',
            'GERENCIA ACTUAL',
            'DEPARTAMENTO ACTUAL',
            'SALARIO ACTUAL',
            'ITEM PROPUESTO',
            'DENOMINACION DEL PUESTO PROPUESTO',
            'GERENCIA DEL PUESTO PROPUESTO',
            'DEPARTAMENTO DEL PUESTO PROPUESTO',
            'SALARIO DEL PUESTO PROPUESTO',
            'FORMACIÓN DEL ITEM PROPUESTO',
            'EXP. PROFESIONAL DEL ITEM PROPUESTO',
            'EXP. RELACIONADA AL AREA DE FORMACIÓN DEL ITEM PROPUESTO',
            'EXP. EN FUNCIONES DE MANDO DEL ITEM PROPUESTO',
            'OBSERVACIÓN DE EVALUACIÓN',
            'DETALLE DE OBSERVACIÓN DE EVALUACIÓN',
            'FECHA DE OBSERVACIÓN DE EVALUACIÓN',
            'RESPONSABLE',
        ];
    }

    protected function collectionCambioItem()
    {
        return $this->incorporaciones->filter(function ($incorporacion) {
            return !is_null($incorporacion->puesto_nuevo_id) && !is_null($incorporacion->puesto_actual_id);
        })->map(function ($incorporacion) {
            return $this->formatDataCambioItem($incorporacion);
        })->toArray();
    }

    protected function formatDataCambioItem($incorporacion)
    {

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

        $datos = [
            'NOMBRE' => mb_strtoupper($incorporacion->persona->nombre_persona . ' ' . $incorporacion->persona->primer_apellido_persona . ' ' . $incorporacion->persona->segundo_apellido_persona),
            'FORMACIÓN ACADÉMICA' => mb_strtoupper($incorporacion->persona->profesion_persona),
            'FECHA DE LA ULTIMA DESIGNACIÓN' => $incorporacion->persona->funcionario->first()->fch_inicio_puesto_funcionario,
            'ITEM ACTUAL' => $incorporacion->puesto_actual->item_puesto,
            'DENOMINACION DEL PUESTO ACTUAL' => $incorporacion->puesto_actual->denominacion_puesto,
            'GERENCIA ACTUAL' => $incorporacion->puesto_actual->departamento->gerencia->nombre_gerencia,
            'DEPARTAMENTO ACTUAL' => $incorporacion->puesto_actual->departamento->nombre_departamento,
            'SALARIO ACTUAL' => $incorporacion->puesto_actual->salario_puesto,
            'ITEM PROPUESTO' => $incorporacion->puesto_nuevo->item_puesto,
            'DENOMINACION DEL PUESTO PROPUESTO' => $incorporacion->puesto_nuevo->denominacion_puesto,
            'GERENCIA DEL PUESTO PROPUESTO' => $incorporacion->puesto_nuevo->departamento->gerencia->nombre_gerencia,
            'DEPARTAMENTO DEL PUESTO PROPUESTO' => $incorporacion->puesto_nuevo->departamento->nombre_departamento,
            'SALARIO DEL PUESTO PROPUESTO' => $incorporacion->puesto_nuevo->salario_puesto,
            'FORMACIÓN DEL ITEM PROPUESTO' => '',
            'EXP. PROFESIONAL DEL ITEM PROPUESTO' => '',
            'EXP. RELACIONADA AL AREA DE FORMACIÓN DEL ITEM PROPUESTO' => '',
            'EXP. EN FUNCIONES DE MANDO DEL ITEM PROPUESTO' => '',
            'OBSERVACIÓN DE EVALUACIÓN' => empty($incorporacion->obs_evaluacion_incorporacion) ? 'NO SE REGISTRÓ EVALUACIÓN' : mb_strtoupper($incorporacion->obs_evaluacion_incorporacion),
            'DETALLE DE OBSERVACIÓN DE EVALUACIÓN' => empty($incorporacion->detalle_obs_evaluacion_incorporacion) ? 'NO SE REGISTRÓ EL DETALLE DE EVALUACIÓN' : mb_strtoupper($incorporacion->detalle_obs_evaluacion_incorporacion),
            'FECHA DE OBSERVACIÓN DE EVALUACIÓN' => empty($incorporacion->fch_obs_evaluacion_incorporacion) ? 'NO SE REGISTRÓ LA FCH. DE EVALUACIÓN' : $incorporacion->fch_obs_evaluacion_incorporacion,
            'RESPONSABLE' => $incorporacion->createdBy->name,
        ];

        foreach ($incorporacion->puesto_nuevo->requisitos as $requisito) {
            if ($requisito) {
                $datos['FORMACIÓN DEL ITEM PROPUESTO'] = $requisito->formacion_requisito;
                $datos['EXP. PROFESIONAL DEL ITEM PROPUESTO'] = $requisito->exp_cargo_requisito;
                $datos['EXP. RELACIONADA AL AREA DE FORMACIÓN DEL ITEM PROPUESTO'] = $requisito->exp_area_requisito;
                $datos['EXP. EN FUNCIONES DE MANDO DEL ITEM PROPUESTO'] = $requisito->exp_mando_requisito;
                break;
            }
        }

        return $datos;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:U1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Tahoma', 'size' => 10],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '012e58']]
        ]);

        $sheet->setAutoFilter('A1:U1');
        $sheet->setTitle('Cambio de Item');

        $lastRow = $sheet->getHighestRow();
        if ($lastRow >= 2) {
            $sheet->getStyle('H2:H' . $lastRow)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('M2:M' . $lastRow)->getNumberFormat()->setFormatCode('#,##0');
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

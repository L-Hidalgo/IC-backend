<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CambioItemTrimestralSheet implements FromArray, WithHeadings, WithStyles
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
            'FECHA DE INCORPORACIÓN',
            'RESPONSABLE',
            'OBSERVACIÓN'
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
        $carbonFechaIncorporacion = null;
        $fechaIncorporacionFormateada = '';

        if (!empty($incorporacion->fch_incorporacion)) {
            $carbonFechaIncorporacion = Carbon::parse($incorporacion->fch_incorporacion);
            setlocale(LC_TIME, 'es_UY');
            $carbonFechaIncorporacion->locale('es_UY');
            $fechaIncorporacionFormateada = $carbonFechaIncorporacion->isoFormat('LL');
        } else {
            $fechaIncorporacionFormateada = 'NO SE REGISTRÓ LA FECHA DE INCORPORACIÓN';
        }


        $datos = [
            'NOMBRE' => mb_strtoupper($incorporacion->persona->nombre_persona . ' ' . $incorporacion->persona->primer_apellido_persona . ' ' . $incorporacion->persona->segundo_apellido_persona),
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
            'FECHA DE INCORPORACIÓN' => $fechaIncorporacionFormateada,
            'RESPONSABLE' => $incorporacion->user->name,
            'OBSERVACIÓN' => ' '
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
        $sheet->getStyle('A1:R1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A7BA8']]
        ]);

        $sheet->setAutoFilter('A1:R1');

        $sheet->setTitle('Cambio de Item');

        $lastRow = $sheet->getHighestRow();
        if ($lastRow >= 2) {
            $sheet->getStyle('F2:F' . $lastRow)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('K2:K' . $lastRow)->getNumberFormat()->setFormatCode('#,##0');
        }

        return [];
    }
}

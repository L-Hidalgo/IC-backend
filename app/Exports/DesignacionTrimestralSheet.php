<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DesignacionTrimestralSheet implements FromArray, WithHeadings, WithStyles
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
            'ITEM',
            'DENOMINACION DEL PUESTO',
            'GERENCIA',
            'DEPARTAMENTO',
            'SALARIO',
            'FORMACIÓN DEL ITEM',
            'EXP. PROFESIONAL  DEL ITEM',
            'EXP. RELACIONADA AL AREA DE FORMACIÓN  DEL ITEM',
            'EXP. EN FUNCIONES DE MANDO  DEL ITEM',
            'FECHA DE INCORPORACIÓN',
            'RESPONSABLE',
            'OBSERVACIÓN'
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
            'ITEM' => $incorporacion->puesto_nuevo->item_puesto,
            'DENOMINACION DEL PUESTO' => $incorporacion->puesto_nuevo->denominacion_puesto,
            'GERENCIA' => $incorporacion->puesto_nuevo->departamento->gerencia->nombre_gerencia,
            'DEPARTAMENTO' => $incorporacion->puesto_nuevo->departamento->nombre_departamento,
            'SALARIO' => $incorporacion->puesto_nuevo->salario_puesto,
            'FORMACIÓN DEL ITEM' => '',
            'EXP. PROFESIONAL  DEL ITEM' => '',
            'EXP. RELACIONADA AL AREA DE FORMACIÓN  DEL ITEM' => '',
            'EXP. EN FUNCIONES DE MANDO DEL ITEM' => '',
            'FECHA DE INCORPORACIÓN' => $fechaIncorporacionFormateada,
            'RESPONSABLE' => $incorporacion->user->name,
            'OBSERVACIÓN' => ' '
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
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A59A8']]
        ]);

        $sheet->setAutoFilter('A1:M1');

        $sheet->setTitle('Designación');

        $lastRow = $sheet->getHighestRow();
        if ($lastRow >= 2) {
            $sheet->getStyle('F2:F' . $lastRow)->getNumberFormat()->setFormatCode('#,##0');
        }

        return [];
    }
}

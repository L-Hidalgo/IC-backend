<?php

namespace App\Exports;

use App\Models\Interinato;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class InterinatosExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    public function collection()
    {
        return Interinato::with(['personaActual', 'puestoActual', 'puestoNuevo', 'createdBy'])
            ->get()
            ->map(function ($interinato, $index) {
                return [
                    $index + 1,
                    $interinato->personaActual ?
                        $interinato->personaActual->nombre_persona . ' ' .
                        $interinato->personaActual->primer_apellido_persona . ' ' .
                        $interinato->personaActual->segundo_apellido_persona :
                        null,
                    $interinato->personaActual ? $interinato->personaActual->ci_persona : null,
                    $interinato->puestoNuevo ? $interinato->puestoNuevo->item_puesto : null,
                    $interinato->puestoNuevo ? $interinato->puestoNuevo->denominacion_puesto : null,
                    $interinato->puestoNuevo ? $interinato->puestoNuevo->departamento->nombre_departamento : null,
                    $interinato->puestoNuevo ? $interinato->puestoNuevo->departamento->gerencia->nombre_gerencia : null,
                    $interinato->puestoNuevo ? $interinato->puestoNuevo->salario_puesto : null,
                    $interinato->puestoNuevo ? $interinato->puestoNuevo->salario_literal_puesto : null,
                    $interinato->puestoActual ? $interinato->puestoActual->item_puesto : null,
                    $interinato->puestoActual ? $interinato->puestoActual->denominacion_puesto : null,
                    $interinato->puestoActual ? $interinato->puestoActual->departamento->nombre_departamento : null,
                    $interinato->puestoActual ? $interinato->puestoActual->departamento->gerencia->nombre_gerencia : null,
                    $interinato->puestoActual ? $interinato->puestoActual->salario_puesto : null,
                    $interinato->puestoActual ? $interinato->puestoActual->salario_literal_puesto : null,
                    $interinato->fch_inicio_interinato,
                    $interinato->fch_fin_interinato,
                    $this->getEstado($interinato->estado_interinato),
                    $interinato->createdBy ? $interinato->createdBy->name : null,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'No.',
            'CI de Persona',
            'Persona',
            'Puesto Destino Item',
            'Puesto Destino Denominación',
            'Puesto Destino Departamento',
            'Puesto Destino Gerencia',
            'Puesto Destino Salario',
            'Puesto Destino Salario Literal',
            'Puesto Actual Item',
            'Puesto Actual Denominación',
            'Puesto Actual Departamento',
            'Puesto Actual Gerencia',
            'Puesto Actual Salario',
            'Puesto Actual Salario Literal',
            'Fecha Inicio',
            'Fecha Fin',
            'Estado',
            'Encargado'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:S1')->applyFromArray([
            'font' => [
                'name' => 'Tahoma',
                'size' => 10,
                'color' => ['argb' => Color::COLOR_WHITE],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => '012e58'], 
            ],
        ]);

        $sheet->getStyle('D1:I1')->applyFromArray([
            'font' => [
                'name' => 'Tahoma',
                'size' => 10,
                'color' => ['argb' => Color::COLOR_WHITE],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => '00BFFF'], 
            ],
        ]);

        $sheet->getStyle('J1:O1')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFA07A'], 
            ],
        ]);
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $event->sheet->getDelegate()->setAutoFilter('A1:S1');
            },
        ];
    }

    private function getEstado($estado)
    {
        switch ($estado) {
            case 1:
                return 'Nuevo';
            case 2:
                return 'Finalizado';
            case 3:
                return 'Suspendido';
            case 4:
                return 'Eliminado';
            default:
                return 'Desconocido';
        }
    }
}

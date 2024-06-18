<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\Exportable;

class IncorporacionesExport implements FromArray, WithHeadings, WithStyles
{
    use Exportable;

    protected $incorporaciones;

    public function __construct($incorporaciones)
    {
        $this->incorporaciones = $incorporaciones;
    }

    public function array(): array
    {
        $designacionData = $this->collectionDesignacion();
        $cambioItemData = $this->collectionCambioItem();

        return array_merge($designacionData, $cambioItemData);
    }


    public function headings(): array
    {
        return [
            'Designacion' => $this->headingsDesignacion(),
            'Cambio de Item' => $this->headingsCambioItem(),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        if ($sheet->getParent()->getIndex($sheet) == 0) {
            // Estilos para la hoja 'Designacion'
            return [
                1 => [
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A59A8']]
                ],
            ];
        } elseif ($sheet->getParent()->getIndex($sheet) == 1) {
            // Estilos para la hoja 'Cambio de Item'
            return [
                1 => [
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A59A8']]
                ],
            ];
        }

        return [];
    }

    protected function collectionDesignacion()
    {
        return $this->incorporaciones->filter(function ($incorporacion) {
            return !is_null($incorporacion->puesto_nuevo_id) && is_null($incorporacion->puesto_actual_id);
        })->map(function ($incorporacion) {
            return $this->formatDataDesignacion($incorporacion);
        })->toArray();
    }

    protected function collectionCambioItem()
    {
        return $this->incorporaciones->filter(function ($incorporacion) {
            return !is_null($incorporacion->puesto_nuevo_id) && !is_null($incorporacion->puesto_actual_id);
        })->map(function ($incorporacion) {
            return $this->formatDataCambioItem($incorporacion);
        })->toArray();
    }

    protected function formatDataDesignacion($incorporacion)
    {
        $fechaNacimiento = Carbon::parse($incorporacion->persona->fch_nacimiento_persona);
        $edad = $fechaNacimiento->age;

        $datos = [
            'NOMBRE' => mb_strtoupper($incorporacion->persona->nombre_persona . ' ' . $incorporacion->persona->primer_apellido_persona . ' ' . $incorporacion->persona->segundo_apellido_persona),
            'EDAD' => $edad . ' AÑOS',
            'FORMACIÓN ACADÉMICA' => mb_strtoupper($incorporacion->persona->formacion[0]->gradoAcademico->nombre_grado_academico) ?? 'NO SE REGISTRO FORMACIÓN ACADÉMICA',
            'EXPERIENCIA' => $incorporacion->experiencia_incorporacion == 0 ? 'NO CUENTA CON EXPERIENCIA EN SERVICIO DE IMPUESTOS NACIONALES' : 'SI CUENTA CON EXPERIENCIA EN IMPUESTOS NACIONALES',
            'ITEM' => $incorporacion->puesto_nuevo->item_puesto,
            'DENOMINACION DEL PUESTO' => $incorporacion->puesto_nuevo->denominacion_puesto,
            'GERENCIA' => $incorporacion->puesto_nuevo->departamento->gerencia->nombre_gerencia,
            'DEPARTAMENTO' => $incorporacion->puesto_nuevo->departamento->nombre_departamento,
            'SALARIO' => $incorporacion->puesto_nuevo->salario_puesto,
            'FORMACIÓN DEL ITEM' => '',
            'EXP. PROFESIONAL' => '',
            'EXP. RELACIONADA AL AREA DE FORMACIÓN' => '',
            'EXP. EN FUNCIONES DE MANDO' => '',
            'OBSERVACIÓN' => mb_strtoupper($incorporacion->observacion_incorporacion),
            'DETALLE DE OBSERVACIÓN' => mb_strtoupper($incorporacion->observacion_detalle_incorporacion),
            'RESPONSABLE' => $incorporacion->user->name,
        ];

        foreach ($incorporacion->puesto_nuevo->requisitos as $requisito) {
            if ($requisito) {
                $datos['FORMACIÓN DEL ITEM'] = $requisito->formacion_requisito;
                $datos['EXP. PROFESIONAL'] = $requisito->exp_cargo_requisito;
                $datos['EXP. RELACIONADA AL AREA DE FORMACIÓN'] = $requisito->exp_area_requisito;
                $datos['EXP. EN FUNCIONES DE MANDO'] = $requisito->exp_mando_requisito;
                break;
            }
        }

        return $datos;
    }

    protected function formatDataCambioItem($incorporacion)
    {
        $fechaNacimiento = Carbon::parse($incorporacion->persona->fch_nacimiento_persona);
        $edad = $fechaNacimiento->age;

        $datos = [
            'NOMBRE' => mb_strtoupper($incorporacion->persona->nombre_persona . ' ' . $incorporacion->persona->primer_apellido_persona . ' ' . $incorporacion->persona->segundo_apellido_persona),
            'FORMACIÓN ACADÉMICA' => mb_strtoupper($incorporacion->persona->profesion_persona),
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
            'OBSERVACIÓN' => mb_strtoupper($incorporacion->observacion_incorporacion),
            'DETALLE DE OBSERVACIÓN' => mb_strtoupper($incorporacion->observacion_detalle_incorporacion),
            'RESPONSABLE' => $incorporacion->user->name,
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

    protected function headingsDesignacion(): array
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
            'EXP. PROFESIONAL',
            'EXP. RELACIONADA AL AREA DE FORMACIÓN',
            'EXP. EN FUNCIONES DE MANDO',
            'OBSERVACIÓN',
            'DETALLE DE OBSERVACIÓN',
            'RESPONSABLE',
        ];
    }

    protected function headingsCambioItem(): array
    {
        return [
            'NOMBRE',
            'FORMACIÓN ACADÉMICA',
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
            'OBSERVACIÓN',
            'DETALLE DE OBSERVACIÓN',
            'RESPONSABLE',
        ];
    }
}

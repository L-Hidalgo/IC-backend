<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReportEvaluacionExport implements WithMultipleSheets
{
    protected $incorporaciones;

    public function __construct($incorporaciones)
    {
        $this->incorporaciones = $incorporaciones;
    }

    public function sheets(): array
    {
        return [
            'Designacion' => new DesignacionEvaluacionSheet($this->incorporaciones), 
            'Cambio de Item' => new CambioItemEvaluacionSheet($this->incorporaciones),
        ];
    }
}

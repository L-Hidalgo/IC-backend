<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReportTrimestralExport implements WithMultipleSheets
{
    protected $incorporaciones;

    public function __construct($incorporaciones)
    {
        $this->incorporaciones = $incorporaciones;
    }

    public function sheets(): array
    {
        return [
            'Designacion' => new DesignacionTrimestralSheet($this->incorporaciones), 
            'Cambio de Item' => new CambioItemTrimestralSheet($this->incorporaciones),
        ];
    }
}

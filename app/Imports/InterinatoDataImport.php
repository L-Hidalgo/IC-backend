<?php

namespace App\Imports;

use App\Models\Interinato;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;

class InterinatoDataImport implements ToModel
{
    protected $messages = []; // Para almacenar los mensajes de interinatos duplicados

    public function startRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {
        $interinato = $this->migrarInterinato($row[5], $row[13], $row[34], $row[35]);
    }

    public function migrarInterinato($puestoNuevoId, $puestoActualId, $fchInicioInterinato, $fchFinInterinato)
    {
        try {
            $interinato = Interinato::where('puesto_nuevo_id', $puestoNuevoId)
                ->where('puesto_actual_id', $puestoActualId)
                ->where('fch_inicio_interinato', $fchInicioInterinato)
                ->where('fch_fin_interinato', $fchFinInterinato)
                ->first();

            if ($interinato) {
                return null;
            }

            return Interinato::create([
                'puesto_nuevo_id' => $puestoNuevoId,
                'puesto_actual_id' => $puestoActualId,
                'fch_inicio_interinato' => $fchInicioInterinato,
                'fch_fin_interinato' => $fchFinInterinato,
                'estado_designacion_interinato' => 0,
            ]);
        } catch (\Exception $e) {
            Log::error("Error al migrar interinato: " . $e->getMessage());
            return null;
        }
    }
}

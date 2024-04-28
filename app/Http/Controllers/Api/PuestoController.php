<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Puesto;
use App\Models\Requisito;
use Illuminate\Support\Facades\Log;

class PuestoController extends Controller
{
    public function getList()
    {
        $puestos = Puesto::select(['denominacion_puesto', 'item_puesto', 'id'])->get();
        return $this->sendSuccess($puestos);
    }

    public function getById($puestoId)
    {
        $puesto = Puesto::with(['persona_actual'])->select(['denominacion_puesto', 'item_puesto', 'id', 'persona_actual_id'])->find($puestoId);
        return $this->sendSuccess($puesto);
    }

    /*public function getByItem($item_puesto) {
        $puesto = Puesto::with(['persona_actual:id_persona,nombre_persona,primer_apellido_persona,segundo_apellido_persona,departamento:id_departamento,nombre_departamento,departamento.gerencia:id_gerencia,nombre_gerencia'])->where('item_puesto', $item_puesto)->first();
        Log::info($puesto);
        return $this->sendObject($puesto);
    } */

    public function getByItem($item_puesto)
    {
        $puesto = Puesto::where('item_puesto', $item_puesto)
        ->with('departamento', 'departamento.gerencia', 'persona_actual')
        ->first();

        if ($puesto) {
            Log::info($puesto);
            return $this->sendObject($puesto);
        } else {
            return null;
        }
    }

    public function getByItemActual($item_puesto)
    {
        $puesto = Puesto::where('item_puesto', $item_puesto)
            ->with('departamento', 'departamento.gerencia', 'persona_actual')
            ->first();

        if ($puesto) {
            Log::info($puesto);
            return $this->sendObject($puesto);
        } else {
            return null;
        }
    }


    public function getRequisitoPuesto($puestoId)
    {
        $requisito = Requisito::where('puesto_id', $puestoId)->first();
        return $this->sendObject($requisito);
    }
}

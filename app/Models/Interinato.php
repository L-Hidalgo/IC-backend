<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Interinato extends Model
{
  use HasFactory;

  protected $table = 'dde_interinatos';

  protected $primaryKey = 'id_interinato';

  protected $fillable = [
    'puesto_nuevo_id',    //importante
    'titular_puesto_nuevo_id',
    'puesto_actual_id',   //importante
    'titular_puesto_actual_id',
    //Desginacion
    'proveido_tramite_interinato',
    'cite_nota_informe_minuta_interinato',
    'fch_cite_nota_inf_minuta_interinato',
    'cite_informe_interinato',
    'fojas_informe_interinato',
    'cite_memorandum_interinato',
    'codigo_memorandum_interinato',
    'cite_rap_interinato',
    'codigo_rap_interinato',
    'fch_memorandum_rap_interinato',
    'fch_inicio_interinato',  //importante
    'fch_fin_interinato',     //importante
    'total_dias_interinato',
    'periodo_interinato',
    'estado_designacion_interinato', // 0: nuevo, 1:enDestino, 2:finalizado, 3:suspendido
    'tipo_nota_informe_minuta_interinato',
    'observaciones_interinato',
    //Suspencion
    'proveido_tramite_interinato',
    'fch_proveido_tramite_interinato_suspencion',
    'cite_memorandum_interinato_suspencion',
    'codigo_memorandum_interinato_suspencion',
    'fch_memorandum_interinato_suspencion',
    'fch_suspencion',
    'codigo_suspencion',
    'fch_designacion_suspencion',
    'motivo_suspencion',

    'sayri_interinato',
    'created_by_interinato',
    'modified_by_interinato',
  ];

  public function puestoNuevo()
  {
    return $this->belongsTo(Puesto::class, 'puesto_nuevo_id', 'id_puesto');
  }

  public function puestoActual()
  {
    return $this->belongsTo(Puesto::class, 'puesto_actual_id', 'id_puesto');
  }

  public function personaNuevo()
  {
    return $this->belongsTo(Persona::class, 'titular_puesto_nuevo_id', 'id_persona');
  }

  public function personaActual()
  {
    return $this->belongsTo(Persona::class, 'titular_puesto_actual_id', 'id_persona');
  }

  public function usuarioCreador()
  {
    return $this->belongsTo(User::class, 'created_by_interinato', 'id');
  }

  public function usuarioModificador()
  {
    return $this->belongsTo(User::class, 'modified_by_interinato', 'id');
  }

  public function actualizarInterinatoDestino()
  {
    $fechaActual = Carbon::now();
    if ($this->fch_inicio_interinato <= $fechaActual->toDateString() && $this->estado === 0) {
      
      if ($this->puesto_actual_id && $this->puesto_nuevo_id) {
        $puestoActual = $this->puestoActual;
        $puestoNuevo = $this->puestoNuevo;

        if ($puestoActual && $puestoNuevo) {
          $puestoNuevo->persona_actual_id = $puestoActual->persona_actual_id;
          $puestoNuevo->denominacion_puesto = $puestoNuevo->denominacion_puesto . ' a.i.';
          $puestoNuevo->estado_id = 2;
          $puestoNuevo->save();

          $puestoActual->persona_actual_id = null;
          $puestoActual->estado_id = 1;
          $puestoActual->save();

          $this->estado = 1; 
          $this->save();
          return true;
        }
      }
    }
    return false;
  }

  public function actualizarInterinatoOrigen()
  {
    $fechaActual = Carbon::now();
    if ($this->fch_fin_interinato <= $fechaActual->toDateString() && $this->estado === 1) {
      if ($this->puesto_actual_id && $this->puesto_nuevo_id) {
        $puestoActual = $this->puestoActual;
        $puestoNuevo = $this->puestoNuevo;
        if ($puestoActual && $puestoNuevo) {
          $puestoNuevo->persona_actual_id = $this->titular_puesto_nuevo_id;
          $puestoNuevo->denominacion_puesto = str_replace(' a.i.', '', $puestoNuevo->denominacion_puesto);

          if (!is_null($this->titular_puesto_nuevo_id) && $this->titular_puesto_nuevo_id !== '') {
            $puestoNuevo->estado_id = 2;
          } else {
            $puestoNuevo->estado_id = 1;
          }
          $puestoNuevo->save();

          $puestoActual->persona_actual_id = $this->titular_puesto_actual_id;
          if (!is_null($this->titular_puesto_actual_id) && $this->titular_puesto_actual_id !== '') {
            $puestoActual->estado_id = 2;
          } else {
            $puestoActual->estado_id = 1;
          }
          $puestoActual->save();

          $this->estado = 2; 
          $this->save();
          return true;
        }
      }
    }
    return false;
  }
}

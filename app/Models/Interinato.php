<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interinato extends Model
{
  use HasFactory;

  protected $table = 'dde_interinatos';

  protected $primaryKey = 'id_interinato';
  
  protected $fillable = [
    'id_interinato',
    'puesto_nuevo_id',
    'puesto_actual_id',
    'persona_id',
    'fch_inicio_interinato',
    'fch_fin_interinato',
    'estado_interinato', // 1: Nuevo, 2: finalizado, 3: finalizado, 4:eliminado

    'cite_informe_instruccion_interinato',
    'fch_informe_instruccion_interinato',
    'proveido_interinato',
    'num_tramite_hp_interinato',
    'cite_informe_interinato',
    'fch_cite_informe_interinato',
    'num_fojas_informe_interinato',

    'cite_rap_interinato',
    'codigo_rap_interinato',
    'num_fojas_rap_interinato',

    'cite_mem_interinato',
    'codigo_mem_interinato',
    'codigo_file_interinato',
    'fch_memorandum_rap_interinato',

    'cite_suspencion_interinato',
    'codigo_suspencion_interinato',
    'fch_suspencion_interinato',
    'codigo_file_suspencion_interinato',
    'created_interinato',
    'modified_interinato',
  ];

  public $timestamps = true;

  protected $dates = [
    'fch_inicio_interinato',
    'fch_fin_interinato',
    'fch_informe_instruccion_interinato',
    'fch_cite_informe_interinato',
    'fch_memorandum_rap_interinato',
    'fch_suspencion_interinato',
  ];
  
  public function personaActual()
  {
    return $this->belongsTo(Persona::class, 'persona_id', 'id_persona');
  }

  public function puestoActual()
  {
    return $this->belongsTo(Puesto::class, 'puesto_actual_id', 'id_puesto');
  }

  public function puestoNuevo()
  {
    return $this->belongsTo(Puesto::class, 'puesto_nuevo_id', 'id_puesto');
  }

  public function createdBy()
  {
    return $this->belongsTo(User::class, 'created_interinato', 'id');
  }

  public function modifiedBy()
  {
    return $this->belongsTo(User::class, 'modified_interinato', 'id');
  }
}

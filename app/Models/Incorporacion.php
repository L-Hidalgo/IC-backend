<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incorporacion extends Model
{
    use HasFactory;

    protected $table = 'dde_incorporaciones';

    protected $primaryKey = 'id_incorporacion';

    protected $fillable = [

        'persona_id',
        'puesto_actual_id',
        'puesto_nuevo_id',
        //datos de evaluacion  cambiaron de nombre 
        'obs_evaluacion_incorporacion', 
        'obs_evaluacion_detalle_incorporacion', 
        'fch_obs_evaluacion_incorporacion',
        'exp_evaluacion_incorporacion', 
        //------------
        'estado_incorporacion',
        'cumple_exp_profesional_incorporacion',
        'cumple_exp_especifica_incorporacion',
        'cumple_exp_mando_incorporacion',
        'cumple_formacion_incorporacion',
        'fch_incorporacion',
        'hp_incorporacion',
        'cite_nota_minuta_incorporacion',
        'codigo_nota_minuta_incorporacion',
        'fch_nota_minuta_incorporacion',
        'fch_recepcion_nota_incorporacion',
        'cite_informe_incorporacion',
        'fch_informe_incorporacion',
        'cite_memorandum_incorporacion',
        'codigo_memorandum_incorporacion',
        'fch_memorandum_incorporacion',
        'cite_rap_incorporacion',
        'codigo_rap_incorporacion',
        'fch_rap_incorporacion',
       
        
        
        'user_id',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id', 'id_persona');
    }

    public function puesto_actual()
    {
        return $this->belongsTo(Puesto::class, 'puesto_actual_id', 'id_puesto');
    }

    public function puesto_nuevo()
    {
        return $this->belongsTo(Puesto::class, 'puesto_nuevo_id', 'id_puesto');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}

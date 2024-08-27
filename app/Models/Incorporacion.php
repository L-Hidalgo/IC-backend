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
        'detalle_obs_evaluacion_incorporacion', 
        'fch_obs_evaluacion_incorporacion',
        'exp_evaluacion_incorporacion', 
        //Para otro documentos es importante
        'fch_incorporacion',
        'hp_incorporacion',
        'n_tramite_incorporcion',
        'cite_informe_incorporacion',
        'fch_informe_incorporacion',
        //inf con minuta y nota 
        'cumple_exp_profesional_incorporacion',
        'cumple_exp_especifica_incorporacion',
        'cumple_exp_mando_incorporacion',
        'cumple_formacion_incorporacion',
        'cite_nota_minuta_incorporacion',
        'codigo_nota_minuta_incorporacion',
        'fch_nota_minuta_incorporacion',
        'fch_recepcion_nota_incorporacion',
        //rap
        'cite_rap_incorporacion',
        'codigo_rap_incorporacion',
        'fch_rap_incorporacion',
       //memo
       'cite_memorandum_incorporacion',
       'codigo_memorandum_incorporacion',
       'fch_memorandum_incorporacion',    
           
        'created_by_incorporacion',
        'modified_by_incorporacion',
        'estado_incorporacion',
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

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_incorporacion');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modified_by_incorporacion');
    }
}

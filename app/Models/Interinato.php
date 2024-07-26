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
        'proveido_tramite_interinato',
        'cite_nota_informe_minuta_interinato',
        'fch_cite_nota_inf_minuta_interinato',
        'puesto_nuevo_id',    //importante
        'titular_puesto_nuevo_id',
        'puesto_actual_id',   //importante
        'titular_puesto_actual_id',
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
        'created_by',
        'modified_by',
        'tipo_nota_informe_minuta_interinato',
        'observaciones_interinato',
        'sayri_interinato',
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
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function usuarioModificador()
    {
        return $this->belongsTo(User::class, 'modified_by', 'id');
    }
}

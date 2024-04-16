<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AreaFormacion extends Model
{
    use HasFactory;

    protected $table = 'dde_area_formaciones';

    protected $primaryKey = 'id_area_formacion';

    protected $fillable = [
        'nombre_area_formacion',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function formacion()
    {
        return $this->hasMany(Formacion::class);
    }

    public function personas()
    {
        return $this->belongsToMany(Persona::class, 'formacion', 'area_formacion_id', 'persona_id')->withPivot('id', 'institucion_id', 'grado_academico_id', 'gestion_formacion', 'estado_formacion', 'con_respaldo_formacion', 'fecha_inicio', 'fecha_fin');
    }
}

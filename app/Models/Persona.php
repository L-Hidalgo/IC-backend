<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;

    protected $table = 'dde_personas';

    protected $primaryKey = 'id_persona';

    public $timestamps = true;

    protected $fillable = [
        'ci_persona',
        'exp_persona',
        'primer_apellido_persona',
        'segundo_apellido_persona',
        'nombre_persona',
        'profesion_persona',
        'genero_persona',
        'fch_nacimiento_persona',
        'telefono_persona',
        'fecha_inicio',
        'fecha_fin'
        //'imagen',
    ];

    protected $casts = [
        'fch_nacimiento_persona' => 'date',
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function funcionario()
    {
        return $this->hasMany(Funcionario::class);
    }

    public function incorporacionFormulario()
    {
        return $this->hasMany(Incorporacion::class, 'persona_id', 'id_persona');
    }

    public function puestos_actuales()
    {
        return $this->hasMany(Puesto::class, 'persona_actual_id', 'id_persona');
    }

    public function formacion()
    {
        return $this->hasMany(Formacion::class, 'persona_id', 'id_persona');
    }

    public function gradosAcademicos()
    {
        return $this->belongsToMany(GradoAcademico::class, 'formacion', 'persona_id', 'grado_academico_id');
    }

    public function areaFormaciones()
    {
        return $this->belongsToMany(AreaFormacion::class, 'formacion', 'persona_id', 'area_formacion_id')->withPivot('id', 'institucion_id', 'grado_academico_id', 'gestion_formacion', 'estado_formacion', 'con_respaldo_formacion', 'fecha_inicio', 'fecha_fin');
    }

    public function instituciones()
    {
        return $this->belongsToMany(Institucion::class, 'formacion', 'persona_id', 'institucion_id')->withPivot('id', 'grado_academico_id', 'area_formacion_id', 'gestion_formacion', 'estado_formacion', 'con_respaldo_formacion', 'fecha_inicio', 'fecha_fin');
    }

    public function imagenes()
    {
        return $this->hasMany(Imagen::class, 'persona_id', 'id_persona');
    }
}

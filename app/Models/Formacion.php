<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Formacion extends Model
{
    use HasFactory;

    protected $table = 'dde_formaciones';

    protected $primaryKey = 'id_formacion';

    protected $fillable = [
        'persona_id',
        'institucion_id',
        'grado_academico_id',
        'area_formacion_id',
        'gestion_formacion',
        'estado_formacion',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id', 'id_persona');
    }

    public function gradoAcademico()
    {
        return $this->belongsTo(GradoAcademico::class, 'grado_academico_id', 'id_grado_academico');
    }

    public function areaFormacion()
    {
        return $this->belongsTo(AreaFormacion::class, 'area_formacion_id', 'id_area_formacion');
    }

    public function institucion()
    {
        return $this->belongsTo(Institucion::class, 'institucion_id', 'id_institucion');
    }
}

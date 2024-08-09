<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Puesto extends Model
{
    use HasFactory;

    protected $table = 'dde_puestos';

    protected $primaryKey = 'id_puesto';

    protected $fillable = [
        'item_puesto',
        'denominacion_puesto',
        'objetivo_puesto',
        'salario_puesto',
        'salario_literal_puesto',
        'departamento_id',
        'estado_id',
        'persona_actual_id',
        'persona_anterior_id'
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function funcionario()
    {
        return $this->hasMany(Funcionario::class, 'puesto_id', 'id_puesto');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id', 'id_estado');
    }

    public function requisitos()
    {
        return $this->hasMany(Requisito::class, 'puesto_id', 'id_puesto');
    }
 
    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id', 'id_departamento');
    }

    public function persona_actual()
    {
        return $this->belongsTo(Persona::class, 'persona_actual_id', 'id_persona');
    }

    public function incorporacion()
    {
        return $this->hasMany(Incorporacion::class);
    }

    public function setEstadoIdAttribute($value)
    {
        $this->attributes['estado_id'] = $this->persona_actual_id ? 2 : $value;
    }
}

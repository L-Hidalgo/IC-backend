<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    use HasFactory;

    protected $table = 'dde_departamentos';

    protected $primaryKey = 'id_departamento';

    protected $fillable = [
        'nombre_departamento',
        'gerencia_id'
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    public function puesto()
    {
        return $this->hasMany(Puesto::class);
    }

    public function gerencia() {
        return $this->belongsTo(Gerencia::class);
    }

}



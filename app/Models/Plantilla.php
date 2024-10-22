<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plantilla extends Model
{
    use HasFactory;

    protected $table = 'dde_plantillas'; 

    protected $primaryKey = 'id_plantilla'; 

    public $incrementing = true;
    public $timestamps = true; 

    protected $fillable = [
        'nombre_plantilla',
        'version_plantilla',
        'tipo_plantilla',  //1: incorporacion, 2: interinato
        'ruta_plantilla',
        'created_plantilla', 
        'modified_plantilla'
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_plantilla');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modified_plantilla');
    }
}

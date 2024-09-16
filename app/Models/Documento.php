<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    use HasFactory;

    protected $table = 'dde_documentos';

    protected $primaryKey = 'id_documento';
    public $incrementing = true;

    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'nombre_documento',
        'ruta_archivo_documento',
        'tipo_documento',
        'estado_documento',
        'persona_id',
        'incorporacion_id',
        'created_by_documento',
        'modified_by_documento',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function incorporacion()
    {
        return $this->belongsTo(Incorporacion::class, 'incorporacion_id');
    }

    public function createdByDocumento()
    {
      return $this->belongsTo(User::class, 'created_by_documento', 'id');
    }
  
    public function modifiedByDocumento()
    {
      return $this->belongsTo(User::class, 'modified_by_documento', 'id');
    }
}



<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;

    protected $table = 'dde_files';

    protected $primaryKey = 'id_file';
    public $incrementing = true;

    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'nombre_file',
        'ruta_file',
        'tipo_documento_file',
        'tipo_file',
        'persona_id',
        'parent_id',
        'estado_file',
        'created_by_file',
        'modified_by_file'
    ];

    public function parent()
    {
        return $this->belongsTo(File::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(File::class, 'parent_id');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_file', 'id');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modified_by_file', 'id');
    }
}

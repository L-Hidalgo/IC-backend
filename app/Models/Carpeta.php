<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Carpeta extends Model
{
    use HasFactory;

    protected $table = 'dde_carpetas';

    protected $primaryKey = 'id_carpeta';
    public $incrementing = true;

    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'nombre_carpeta',
        'ruta_carpeta',
        'tipo_carpeta',
        'estado_carpeta',
        'padre_id_carpeta',
        'created_by_carpeta',
        'modified_by_carpeta'
    ];

    public function documento()
    {
        return $this->hasMany(Documento::class, 'carpeta_id');
    }

    public function padre()
    {
        return $this->belongsTo(Carpeta::class, 'padre_id_carpeta');
    }

    public function hijo()
    {
        return $this->hasMany(Carpeta::class, 'padre_id_carpeta');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_carpeta', 'id');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modified_by_carpeta', 'id');
    }
}

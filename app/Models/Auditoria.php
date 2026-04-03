<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Auditoria extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'auditorias';

    protected $fillable = [
        'nombre_auditoria',
        'tipo_auditoria',
        'auditor_lider',
        'fecha_auditoria', // Campo existente en la tabla
        'fecha_inicio',
        'fecha_fin',
        'anio',
        'auditores',
        'archivo_path',
        'archivo_nombre'
    ];

    protected $casts = [
        'fecha_auditoria' => 'date',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'anio' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RegistraHistorialVersiones;

class Auditoria extends Model
{
    use HasFactory, RegistraHistorialVersiones;

    protected $table = 'auditorias';

    protected $fillable = [
        'nombre_auditoria',
        'tipo_auditoria',
        'auditor_lider',
        'fecha_inicio',
        'fecha_fin',
        'anio',
        'auditores',
        'archivo_path',
        'archivo_nombre'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'anio' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}
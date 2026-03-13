<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitudMejora extends Model
{
    use HasFactory;

    protected $table = 'solicitudes_mejora';

    protected $fillable = [
        'folio_solicitud',
        'fecha_solicitud',
        'responsable_accion',
        'fecha_aplicacion',
        'actividades_verificacion',
        'fecha_verificacion',
        'estatus',
        'archivo_nombre',
        'archivo_ruta'
    ];

    protected $casts = [
        'fecha_solicitud' => 'date',
        'fecha_aplicacion' => 'date',
        'fecha_verificacion' => 'date'
    ];

    public function getEstatusBadgeClassAttribute()
    {
        return match($this->estatus) {
            'No Atendida' => 'badge-no-atendida',
            'En Proceso'  => 'badge-proceso',
            'Cerrado'     => 'badge-cerrado',
            default       => 'badge-secondary'
        };
    }

    public function getEstatusColorAttribute()
    {
        return match($this->estatus) {
            'No Atendida' => '#fd7e14',
            'En Proceso'  => '#17a2b8',
            'Cerrado'     => '#28a745',
            default       => '#6c757d'
        };
    }
}
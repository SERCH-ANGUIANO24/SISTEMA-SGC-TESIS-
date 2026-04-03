<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\RegistraHistorialVersiones;

class SolicitudMejora extends Model
{
    use HasFactory, SoftDeletes, RegistraHistorialVersiones;

    protected $table = 'solicitudes_mejora';

    protected $fillable = [
        'informe_id',
        'folio_solicitud',
        'fecha_solicitud',
        'responsable_accion',
        'fecha_aplicacion',
        'actividades_verificacion',
        'fecha_verificacion',
        'estatus',
        'archivo_nombre',
        'archivo_ruta',
        'fecha_informe',
        'procesos_auditados',
        'tipo_solicitud',
    ];

    protected $casts = [
        'fecha_solicitud' => 'datetime',
        'fecha_aplicacion' => 'date',
        'fecha_verificacion' => 'date',
        'fecha_informe' => 'date',
        'deleted_at' => 'datetime',
    ];

    public function informe()
    {
        return $this->belongsTo(InformeAuditoria::class, 'informe_id');
    }
}
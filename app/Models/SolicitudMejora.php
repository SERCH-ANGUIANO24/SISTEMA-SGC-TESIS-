<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\RegistraHistorialVersiones;

/**
 * MODELO PARA SOLICITUDES DE MEJORA
 * SE CREAN A PARTIR DE NO CONFORMIDADES U OPORTUNIDADES DETECTADAS EN AUDITORÍAS
 * REGISTRA QUIÉN LAS ATIENDE, FECHAS, ESTATUS Y ARCHIVOS ADJUNTOS
 */
class SolicitudMejora extends Model
{
    use HasFactory, SoftDeletes, RegistraHistorialVersiones;  // SOFTDELETES + HISTORIAL

    // NOMBRE DE LA TABLA EN BASE DE DATOS
    protected $table = 'solicitudes_mejora';

    // CAMPOS QUE SE PUEDEN LLENAR DE FORMA MASIVA
    protected $fillable = [
        'informe_id',               // ID DEL INFORME DE AUDITORÍA QUE LA GENERÓ
        'folio_solicitud',          // NÚMERO ÚNICO DE IDENTIFICACIÓN
        'fecha_solicitud',          // CUÁNDO SE SOLICITÓ LA MEJORA
        'responsable_accion',       // QUIÉN DEBE IMPLEMENTAR LA MEJORA
        'fecha_aplicacion',         // FECHA LÍMITE PARA APLICAR LA MEJORA
        'actividades_verificacion', // QUÉ ACCIONES SE HACEN PARA VERIFICAR
        'fecha_verificacion',       // CUÁNDO SE VERIFICÓ LA IMPLEMENTACIÓN
        'estatus',                  // PENDIENTE, EN PROCESO, COMPLETADA, ETC
        'archivo_nombre',           // NOMBRE ORIGINAL DEL ARCHIVO ADJUNTO
        'archivo_ruta',             // RUTA DONDE ESTÁ GUARDADO EL ARCHIVO
        'fecha_informe',            // FECHA DEL INFORME RELACIONADO
        'procesos_auditados',       // QUÉ PROCESOS ESTÁN INVOLUCRADOS
        'tipo_solicitud',           // NO CONFORMIDAD U OPORTUNIDAD DE MEJORA
    ];

    // CONVIERTE AUTOMÁTICAMENTE ESTOS CAMPOS AL TIPO CORRECTO
    protected $casts = [
        'fecha_solicitud' => 'datetime',
        'fecha_aplicacion' => 'date',
        'fecha_verificacion' => 'date',
        'fecha_informe' => 'date',
        'deleted_at' => 'datetime',
    ];

    /**
     * RELACIÓN: UNA SOLICITUD DE MEJORA PERTENECE A UN INFORME DE AUDITORÍA
     * PERMITE ACCEDER A LOS DATOS DEL INFORME DESDE LA SOLICITUD
     */
    public function informe()
    {
        return $this->belongsTo(InformeAuditoria::class, 'informe_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * MODELO PARA NOTIFICACIONES DEL SISTEMA
 * CADA USUARIO RECIBE NOTIFICACIONES DE EVENTOS IMPORTANTES (NUEVAS AUDITORÍAS, SOLICITUDES, ETC)
 * PUEDEN SER MARCADAS COMO LEÍDAS Y ENVIADAS POR EMAIL
 */
class Notificacion extends Model
{
    // NOMBRE DE LA TABLA EN BASE DE DATOS
    protected $table = 'notificaciones';

    // CAMPOS QUE SE PUEDEN LLENAR DE FORMA MASIVA
    protected $fillable = [
        'user_id',           // A QUIÉN VA DIRIGIDA LA NOTIFICACIÓN
        'tipo',              // TIPO DE NOTIFICACIÓN (INFO, ALERTA, ERROR, ETC)
        'titulo',            // TÍTULO CORTO DE LA NOTIFICACIÓN
        'mensaje',           // TEXTO COMPLETO DE LA NOTIFICACIÓN
        'icono',             // ÍCONO QUE SE MUESTRA (EJ: bi-bell, bi-check-circle)
        'url',               // ENLACE PARA IR A LA SECCIÓN RELACIONADA
        'leida',             // ¿YA FUE VISTA POR EL USUARIO? (FALSE POR DEFECTO)
        'enviada_email',     // ¿YA SE ENVIÓ POR CORREO? (EVITA DUPLICADOS)
        'documento_id',      // ID DEL DOCUMENTO RELACIONADO (OPCIONAL)
        'tipo_evento',       // NOMBRE DEL EVENTO (EJ: nueva_auditoria, solicitud_mejora)
    ];

    // CONVIERTE AUTOMÁTICAMENTE ESTOS CAMPOS A BOOLEANOS
    protected $casts = [
        'leida'         => 'boolean',   // 1/0 O true/false SE VUELVEN VERDADERO/FALSO
        'enviada_email' => 'boolean',   // LO MISMO, SE VUELVE true O false
    ];

    // ── RELACIONES ────────────────────────────────────────────────

    /**
     * UNA NOTIFICACIÓN PERTENECE A UN USUARIO
     * PERMITE ACCEDER AL USUARIO DESDE LA NOTIFICACIÓN: $notificacion->user->name
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── SCOPES (FILTROS REUTILIZABLES) ───────────────────────────────────

    /**
     * FILTRA SOLO LAS NOTIFICACIONES NO LEÍDAS
     * EJEMPLO: Notificacion::noLeidas()->get()
     */
    public function scopeNoLeidas($q)
    {
        return $q->where('leida', false);
    }

    /**
     * FILTRA NOTIFICACIONES DE UN USUARIO ESPECÍFICO
     * EJEMPLO: Notificacion::deUsuario(5)->get()
     */
    public function scopeDeUsuario($q, int $userId)
    {
        return $q->where('user_id', $userId);
    }

    /**
     * FILTRA NOTIFICACIONES POR TIPO DE EVENTO
     * EJEMPLO: Notificacion::delTipo('nueva_auditoria')->get()
     */
    public function scopeDelTipo($q, string $tipo)
    {
        return $q->where('tipo_evento', $tipo);
    }
}
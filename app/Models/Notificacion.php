<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = [
        'user_id',
        'tipo',
        'titulo',
        'mensaje',
        'icono',
        'url',
        'leida',
        'enviada_email',
        'documento_id',
        'tipo_evento',
    ];

    protected $casts = [
        'leida'         => 'boolean',
        'enviada_email' => 'boolean',
    ];

    // ── RELACIONES ────────────────────────────────────────────────
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── SCOPES ───────────────────────────────────────────────────
    public function scopeNoLeidas($q)
    {
        return $q->where('leida', false);
    }

    public function scopeDeUsuario($q, int $userId)
    {
        return $q->where('user_id', $userId);
    }

    public function scopeDelTipo($q, string $tipo)
    {
        return $q->where('tipo_evento', $tipo);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RegistraHistorialVersiones;


class HistorialVersiones extends Model
{
    use HasFactory, RegistraHistorialVersiones; 

    protected $table = 'historial_versiones';

    protected $fillable = [
        'usuario_nombre',
        'usuario_id',
        'usuario_email',
        'usuario_rol',
        'modulo',
        'accion',
        'descripcion',
        'nivel_importancia',
        'datos_anteriores',
        'datos_nuevos',
        'ip_address',
        'user_agent',
        'tabla_afectada',
        'registro_id',
        'elemento_nombre'
    ];

    protected $casts = [
        'datos_anteriores' => 'array',
        'datos_nuevos' => 'array',
        'created_at' => 'datetime'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Scopes y accesores (incluye los que ya tenías)
    public function scopeDelModulo($query, $modulo)
    {
        return $query->where('modulo', $modulo);
    }

    public function scopeConAccion($query, $accion)
    {
        return $query->where('accion', $accion);
    }

    public function scopeDelUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    public function scopeEntreFechas($query, $inicio, $fin)
    {
        return $query->whereBetween('created_at', [$inicio, $fin]);
    }

    public function scopeHoy($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeEstaSemana($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeEsteMes($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    public function getColorAccionAttribute()
    {
        return match($this->accion) {
            'CREAR' => '#10b981',
            'EDITAR' => '#f59e0b',
            'ELIMINAR' => '#ef4444',
            'RESTAURAR' => '#06b6d4',
            'VER' => '#3b82f6',
            'DESCARGAR' => '#8b5cf6',
            default => '#6b7280'
        };
    }

    public function getIconoAccionAttribute()
    {
        return match($this->accion) {
            'CREAR' => 'bi-plus-circle-fill',
            'EDITAR' => 'bi-pencil-fill',
            'ELIMINAR' => 'bi-trash-fill',
            'RESTAURAR' => 'bi-arrow-counterclockwise',
            'VER' => 'bi-eye-fill',
            'DESCARGAR' => 'bi-download',
            default => 'bi-clock-history'
        };
    }

    public function getColorModuloAttribute()
    {
        return match($this->modulo) {
            'ANEXOS' => '#4f46e5',
            'AUDITORIAS' => '#059669',
            'GESTION_DOCUMENTAL' => '#dc2626',
            'MATRIZ' => '#9333ea',
            'FORMATOS' => '#16a34a',
            'USUARIOS' => '#7c3aed',
            'HISTORIAL' => '#0891b2',
            'NOTIFICACIONES' => '#ea580c',
            'AVISOS' => '#4f46e5',
            default => '#6b7280'
        };
    }

    public function getIconoModuloAttribute()
    {
        return match($this->modulo) {
            'ANEXOS' => 'bi-folder',
            'AUDITORIAS' => 'bi-clipboard-check',
            'GESTION_DOCUMENTAL' => 'bi-files',
            'MATRIZ' => 'bi-grid-3x3',
            'FORMATOS' => 'bi-file-earmark-text',
            'USUARIOS' => 'bi-people',
            'HISTORIAL' => 'bi-clock-history',
            'NOTIFICACIONES' => 'bi-bell',
            'AVISOS' => 'bi-megaphone',
            default => 'bi-archive'
        };
    }

    public function getBadgeImportanciaAttribute()
    {
        return match($this->nivel_importancia) {
            'bajo' => '<span class="badge" style="background: #e2e8f0; color: #475569;">BAJO</span>',
            'normal' => '<span class="badge" style="background: #3b82f6; color: white;">NORMAL</span>',
            'alto' => '<span class="badge" style="background: #f97316; color: white;">ALTO</span>',
            'critico' => '<span class="badge" style="background: #ef4444; color: white;">CRÍTICO</span>',
            default => '<span class="badge" style="background: #6b7280; color: white;">' . $this->nivel_importancia . '</span>'
        };
    }

    public function getFechaFormateadaAttribute()
    {
        return $this->created_at->format('d/m/Y H:i:s');
    }

    public function getTiempoRelativoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function huboCambioEn($campo)
    {
        if (!$this->datos_anteriores || !$this->datos_nuevos) return false;
        $anteriores = is_array($this->datos_anteriores) ? $this->datos_anteriores : json_decode($this->datos_anteriores, true);
        $nuevos = is_array($this->datos_nuevos) ? $this->datos_nuevos : json_decode($this->datos_nuevos, true);
        return isset($nuevos[$campo]) && (!isset($anteriores[$campo]) || $anteriores[$campo] != $nuevos[$campo]);
    }

    public function getCambiosRealizados()
    {
        if (!$this->datos_anteriores || !$this->datos_nuevos) return [];
        $anteriores = is_array($this->datos_anteriores) ? $this->datos_anteriores : json_decode($this->datos_anteriores, true);
        $nuevos = is_array($this->datos_nuevos) ? $this->datos_nuevos : json_decode($this->datos_nuevos, true);
        $cambios = [];
        foreach ($nuevos as $campo => $valor) {
            if (!isset($anteriores[$campo]) || $anteriores[$campo] != $valor) {
                $cambios[$campo] = [
                    'anterior' => $anteriores[$campo] ?? null,
                    'nuevo' => $valor
                ];
            }
        }
        return $cambios;
    }
}
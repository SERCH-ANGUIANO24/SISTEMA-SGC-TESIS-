<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InformeAuditoria extends Model
{
    use HasFactory;

    protected $table = 'informes_auditoria';

    protected $fillable = [
        'nombre_informe',
        'tipo_auditoria',
        'auditor_lider',
        'fecha_informe',
        'fecha_auditoria',
        'auditoria_relacionada_id',
        'procesos_auditados',
        'no_conformidades',
        'oportunidades_mejora',
        'nc_om_por_proceso',      // ← NUEVO
        'documento_path',
        'documento_nombre',
    ];

    protected $casts = [
        'fecha_informe'        => 'date',
        'fecha_auditoria'      => 'date',
        'procesos_auditados'   => 'array',
        'no_conformidades'     => 'integer',
        'oportunidades_mejora' => 'integer',
        'nc_om_por_proceso'    => 'array', // ← NUEVO
    ];

    /**
     * Relación con Plan de Auditoría (auditoria relacionada)
     */
    public function auditoriaRelacionada()
    {
        return $this->belongsTo(Auditoria::class, 'auditoria_relacionada_id');
    }

    /**
     * Año derivado de fecha_auditoria
     */
    public function getAnioAttribute(): int
    {
        return $this->fecha_auditoria ? $this->fecha_auditoria->year : 0;
    }

    /**
     * Scope para filtrar por año
     */
    public function scopePorAnio($query, int $anio)
    {
        return $query->whereYear('fecha_auditoria', $anio);
    }

    /**
     * Scope para filtrar por tipo de auditoría
     */
    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo_auditoria', $tipo);
    }

    /**
     * Estadísticas totales por año
     */
    public static function estadisticasPorAnio(int $anio): array
    {
        $informes = self::whereYear('fecha_auditoria', $anio)->get();

        $totalAuditorias     = $informes->count();
        $totalNoConformidades = $informes->sum('no_conformidades');
        $totalOportunidades  = $informes->sum('oportunidades_mejora');

        // Procesos únicos auditados en el año
        $procesosSet = [];
        foreach ($informes as $informe) {
            if ($informe->procesos_auditados) {
                foreach ($informe->procesos_auditados as $proceso) {
                    $procesosSet[$proceso] = true;
                }
            }
        }

        return [
            'anio'                 => $anio,
            'total_auditorias'     => $totalAuditorias,
            'no_conformidades'     => $totalNoConformidades,
            'oportunidades_mejora' => $totalOportunidades,
            'procesos_auditados'   => array_keys($procesosSet),
            'informes'             => $informes,
        ];
    }
}
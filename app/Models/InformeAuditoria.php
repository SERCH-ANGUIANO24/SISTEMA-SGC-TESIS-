<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InformeAuditoria extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'informes_auditoria';

    protected $fillable = [
        'nombre_informe',
        'tipo_auditoria',
        'auditor_lider',
        'fecha_informe',
        'fecha_auditoria',
        'fecha_inicio',
        'fecha_fin',
        'auditoria_relacionada_id',
        'procesos_auditados',
        'no_conformidades',
        'oportunidades_mejora',
        'nc_om_por_proceso',
        'documento_path',
        'documento_nombre',
    ];

    protected $casts = [
        'fecha_informe'        => 'date',
        'fecha_auditoria'      => 'date',
        'fecha_inicio'         => 'date',
        'fecha_fin'            => 'date',
        'procesos_auditados'   => 'array',
        'no_conformidades'     => 'integer',
        'oportunidades_mejora' => 'integer',
        'nc_om_por_proceso'    => 'array',
        'deleted_at'           => 'datetime',
    ];

    public function auditoriaRelacionada()
    {
        return $this->belongsTo(Auditoria::class, 'auditoria_relacionada_id');
    }

    public function getAnioAttribute(): int
    {
        return $this->fecha_auditoria ? $this->fecha_auditoria->year : 0;
    }

    public function scopePorAnio($query, int $anio)
    {
        return $query->whereYear('fecha_auditoria', $anio);
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo_auditoria', $tipo);
    }

    public static function estadisticasPorAnio(int $anio): array
    {
        $informes = self::whereYear('fecha_auditoria', $anio)->get();

        $totalAuditorias     = $informes->count();
        $totalNoConformidades = $informes->sum('no_conformidades');
        $totalOportunidades  = $informes->sum('oportunidades_mejora');

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
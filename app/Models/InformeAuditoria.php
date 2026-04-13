<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * MODELO PARA LOS INFORMES DE AUDITORÍA
 * GUARDA LOS RESULTADOS DE CADA AUDITORÍA: NO CONFORMIDADES, OPORTUNIDADES, PROCESOS AUDITADOS, ETC
 * PUEDE TENER UN ARCHIVO ADJUNTO (EL INFORME COMPLETO EN PDF)
 */
class InformeAuditoria extends Model
{
    use HasFactory, SoftDeletes;  // SOFTDELETES: ELIMINACIÓN 

    // NOMBRE DE LA TABLA EN BASE DE DATOS
    protected $table = 'informes_auditoria';

    // CAMPOS QUE SE PUEDEN LLENAR DE FORMA MASIVA
    protected $fillable = [
        'nombre_informe',            // TÍTULO DEL INFORME
        'tipo_auditoria',            // INTERNA O EXTERNA
        'auditor_lider',             // QUIÉN LIDERÓ LA AUDITORÍA
        'fecha_informe',             // FECHA QUE SE EMITIÓ EL INFORME
        'fecha_auditoria',           // FECHA DE LA AUDITORÍA (COMPATIBILIDAD)
        'fecha_inicio',              // INICIO DE LA AUDITORÍA
        'fecha_fin',                 // FIN DE LA AUDITORÍA
        'auditoria_relacionada_id',  // ID DE LA AUDITORÍA ORIGINAL (TABLA auditorias)
        'procesos_auditados',        // LISTA DE PROCESOS EVALUADOS (ARRAY)
        'no_conformidades',          // CUÁNTAS NO CONFORMIDADES SE ENCONTRARON
        'oportunidades_mejora',      // CUÁNTAS OPORTUNIDADES SE DETECTARON
        'nc_om_por_proceso',         // DESGLOSE POR PROCESO (ARRAY)
        'documento_path',            // RUTA DEL ARCHIVO PDF DEL INFORME
        'documento_nombre',          // NOMBRE ORIGINAL DEL ARCHIVO
    ];

    // CONVIERTE AUTOMÁTICAMENTE ESTOS CAMPOS AL TIPO CORRECTO
    protected $casts = [
        'fecha_informe'        => 'date',
        'fecha_auditoria'      => 'date',
        'fecha_inicio'         => 'date',
        'fecha_fin'            => 'date',
        'procesos_auditados'   => 'array',    // JSON → ARREGLO
        'no_conformidades'     => 'integer',
        'oportunidades_mejora' => 'integer',
        'nc_om_por_proceso'    => 'array',    // JSON → ARREGLO
        'deleted_at'           => 'datetime',
    ];

    /**
     * RELACIÓN: EL INFORME PERTENECE A UNA AUDITORÍA (LA QUE LE DIO ORIGEN)
     * PERMITE ACCEDER A LOS DATOS DEL PLAN DE AUDITORÍA DESDE EL INFORME
     */
    public function auditoriaRelacionada()
    {
        return $this->belongsTo(Auditoria::class, 'auditoria_relacionada_id');
    }

    /**
     * OBTIENE EL AÑO DE LA AUDITORÍA (EXTRAÍDO DE fecha_auditoria)
     * SI NO HAY FECHA, DEVUELVE 0
     */
    public function getAnioAttribute(): int
    {
        return $this->fecha_auditoria ? $this->fecha_auditoria->year : 0;
    }

    // ========== SCOPES (FILTROS REUTILIZABLES) ==========

    /**
     * FILTRA INFORMES POR AÑO
     * EJEMPLO: InformeAuditoria::porAnio(2024)->get()
     */
    public function scopePorAnio($query, int $anio)
    {
        return $query->whereYear('fecha_auditoria', $anio);
    }

    /**
     * FILTRA INFORMES POR TIPO DE AUDITORÍA (INTERNA O EXTERNA)
     * EJEMPLO: InformeAuditoria::porTipo('Interna')->get()
     */
    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo_auditoria', $tipo);
    }

    // ========== MÉTODOS ESTÁTICOS ==========

    /**
     * CALCULA ESTADÍSTICAS COMPLETAS DE UN AÑO ESPECÍFICO
     * DEVUELVE: TOTAL DE AUDITORÍAS, NO CONFORMIDADES, OPORTUNIDADES, PROCESOS AUDITADOS Y LISTA DE INFORMES
     * EJEMPLO: InformeAuditoria::estadisticasPorAnio(2024)
     */
    public static function estadisticasPorAnio(int $anio): array
    {
        // TRAE TODOS LOS INFORMES DEL AÑO
        $informes = self::whereYear('fecha_auditoria', $anio)->get();

        // SUMAS TOTALES
        $totalAuditorias     = $informes->count();
        $totalNoConformidades = $informes->sum('no_conformidades');
        $totalOportunidades  = $informes->sum('oportunidades_mejora');

        // PROCESOS ÚNICOS AUDITADOS (SIN REPETIR)
        $procesosSet = [];
        foreach ($informes as $informe) {
            if ($informe->procesos_auditados) {
                foreach ($informe->procesos_auditados as $proceso) {
                    $procesosSet[$proceso] = true;
                }
            }
        }

        // RETORNA EL RESULTADO
        return [
            'anio'                 => $anio,
            'total_auditorias'     => $totalAuditorias,
            'no_conformidades'     => $totalNoConformidades,
            'oportunidades_mejora' => $totalOportunidades,
            'procesos_auditados'   => array_keys($procesosSet),  // CONVIERTE LAS LLAVES EN ARREGLO
            'informes'             => $informes,
        ];
    }
}
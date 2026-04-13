<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RegistraHistorialVersiones;
use Illuminate\Support\Facades\Route;

/**
 * REGISTRO DE TODAS LAS ACCIONES DE USUARIOS EN EL SISTEMA
 * SIRVE PARA AUDITORÍA, SEGURIDAD Y RASTREO DE CAMBIOS
 */
class HistorialVersiones extends Model
{
    use HasFactory, RegistraHistorialVersiones;  // SE REGISTRA A SÍ MISMO

    // NOMBRE DE LA TABLA
    protected $table = 'historial_versiones';

    // CAMPOS QUE SE PUEDEN LLENAR MASIVAMENTE
    protected $fillable = [
        'usuario_nombre',     // QUIÉN HIZO LA ACCIÓN
        'usuario_id',         // ID DEL USUARIO
        'usuario_email',      // CORREO DEL USUARIO
        'usuario_rol',        // ROL DEL USUARIO
        'modulo',             // MÓDULO AFECTADO
        'accion',             // CREAR, EDITAR, ELIMINAR, VER, ETC
        'descripcion',        // DESCRIPCIÓN LEGIBLE
        'nivel_importancia',  // BAJO, NORMAL, ALTO, CRÍTICO
        'datos_anteriores',   // JSON CON DATOS VIEJOS
        'datos_nuevos',       // JSON CON DATOS NUEVOS
        'ip_address',         // IP DEL USUARIO
        'user_agent',         // NAVEGADOR/SISTEMA OPERATIVO
        'tabla_afectada',     // TABLA DE BD MODIFICADA
        'registro_id',        // ID DEL REGISTRO AFECTADO
        'elemento_nombre'     // NOMBRE DEL ELEMENTO (PARA BÚSQUEDA)
    ];

    // CONVERSIONES AUTOMÁTICAS DE TIPOS
    protected $casts = [
        'datos_anteriores' => 'array',  // JSON → ARREGLO
        'datos_nuevos' => 'array',      // JSON → ARREGLO
        'created_at' => 'datetime'
    ];

    /**
     * MAPA: CÓDIGO DE MÓDULO → NOMBRE 
     */
    protected static $mapaModulos = [
        'USUARIOS'              => 'Usuarios del Sistema',
        'PROCESOS'              => 'Procesos',
        'DEPARTAMENTOS'         => 'Departamentos',
        'SOLICITUDES_MEJORA'    => 'Solicitud de Mejora',
        'MATRICES_DOCUMENTS'    => 'Documento de Matriz',
        'INFORMES_AUDITORIA'    => 'Informes',
        'HISTORIAL'             => 'Historial de Versiones',
        'FORMATOS'              => 'Lista Maestra',
        'FOLDERS'               => 'Anexos',
        'DOCUMENTS'             => 'Anexos',
        'COMPETENCIAS'          => 'Competencia',
        'AUDITORIAS'            => 'Plan de Auditoria',
        'DOCUMENTAL_DOCUMENTS'  => 'Gestión Documental',
        'DOCUMENTALFOLDER'      => 'Gestión Documental',
        'AVISOS'                => 'Avisos',
        'DocumentalFolder'      => 'Gestión Documental',  // COMPATIBILIDAD
        'MatrizFolder'          => 'Carpeta de Matriz',   // COMPATIBILIDAD
    ];

    // RELACIÓN: PERTENECE A UN USUARIO
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * DEVUELVE EL NOMBRE DEL MÓDULO EN VERSIÓN LEGIBLE
     */
    public function getNombreModuloFormateadoAttribute()
    {
        $modulo = $this->modulo;
        
        if (isset(self::$mapaModulos[$modulo])) {
            return self::$mapaModulos[$modulo];
        }
        
        $moduloUpper = strtoupper($modulo);
        if (isset(self::$mapaModulos[$moduloUpper])) {
            return self::$mapaModulos[$moduloUpper];
        }
        
        return ucfirst(strtolower($modulo));
    }

    /**
     * COLOR PARA CADA MÓDULO (ÚTIL PARA INTERFAZ)
     */
    public function getColorModuloAttribute()
    {
        $colores = [
            'USUARIOS'             => '#7c3aed',
            'PROCESOS'             => '#7c3aed',
            'DEPARTAMENTOS'        => '#7c3aed',
            'ANEXOS'               => '#4f46e5',
            'FOLDERS'              => '#4f46e5',
            'DOCUMENTS'            => '#4f46e5',
            'AUDITORIAS'           => '#4f46e5',
            'INFORMES_AUDITORIA'   => '#059669',
            'SOLICITUDES_MEJORA'   => '#dc2626',
            'COMPETENCIAS'         => '#7c3aed',
            'GESTION_DOCUMENTAL'   => '#dc2626',
            'DOCUMENTAL_DOCUMENTS' => '#dc2626',
            'DOCUMENTALFOLDER'     => '#dc2626',
            'DocumentalFolder'     => '#dc2626',
            'MATRIZ'               => '#9333ea',
            'MATRICES_DOCUMENTS'   => '#9333ea',
            'MatrizFolder'         => '#9333ea',
            'FORMATOS'             => '#16a34a',
            'HISTORIAL'            => '#0891b2',
            'NOTIFICACIONES'       => '#ea580c',
            'AVISOS'               => '#4fa6e5',
        ];

        if (isset($colores[$this->modulo])) {
            return $colores[$this->modulo];
        }
        
        $moduloUpper = strtoupper($this->modulo);
        if (isset($colores[$moduloUpper])) {
            return $colores[$moduloUpper];
        }
        
        return '#737373';  
    }

    /**
     * ÍCONO PARA CADA MÓDULO (BOOTSTRAP ICONS)
     */
    public function getIconoModuloAttribute()
    {
        $iconos = [
            'USUARIOS'             => 'bi-people-fill',
            'PROCESOS'             => 'bi-diagram-3-fill',
            'DEPARTAMENTOS'        => 'bi-building-fill',
            'SOLICITUDES_MEJORA'   => 'bi-chat-text-fill',
            'MATRICES_DOCUMENTS'   => 'bi-file-text-fill',
            'MatrizFolder'         => 'bi-folder-fill',
            'INFORMES_AUDITORIA'   => 'bi-file-earmark-text-fill',
            'FORMATOS'             => 'bi-table',
            'FOLDERS'              => 'bi-folder-fill',
            'DocumentalFolder'     => 'bi-folder-fill',
            'DOCUMENTAL_DOCUMENTS' => 'bi-file-earmark-fill',
            'DOCUMENTS'            => 'bi-file-earmark-fill',
            'COMPETENCIAS'         => 'bi-award-fill',
            'AUDITORIAS'           => 'bi-clipboard-check-fill',
            'AVISOS'               => 'bi-megaphone-fill',
        ];

        if (isset($iconos[$this->modulo])) {
            return $iconos[$this->modulo];
        }
        
        $moduloUpper = strtoupper($this->modulo);
        if (isset($iconos[$moduloUpper])) {
            return $iconos[$moduloUpper];
        }
        
        return 'bi-folder';
    }

    /**
     * URL PARA VER EL MÓDULO DONDE SUCEDIÓ LA ACCIÓN
     */
    public function getDetalleUrlAttribute()
    {
        $modulo = $this->modulo;
        $moduloUpper = strtoupper($modulo);
        
        $rutas = [
            'USUARIOS' => 'admin.usuarios.index',
            'PROCESOS' => 'admin.usuarios.index',
            'DEPARTAMENTOS' => 'admin.usuarios.index',
            'ANEXOS' => 'anexos.index',
            'FOLDERS' => 'anexos.index',
            'DOCUMENTS' => 'anexos.index',
            'DOCUMENTAL' => 'documental.index',
            'DOCUMENTAL_DOCUMENTS' => 'documental.index',
            'DOCUMENTALFOLDER' => 'documental.index',
            'MATRIZ' => 'matriz.index',
            'MATRICES_DOCUMENTS' => 'matriz.index',
            'MATRIXFOLDER' => 'matriz.index',
            'FORMATOS' => 'formatos.index',
            'INFORMES_AUDITORIA' => 'informes-auditoria.index',
            'AUDITORIAS' => 'auditoria.plan.index',
            'SOLICITUDES_MEJORA' => 'auditoria.solicitudes.index',
            'COMPETENCIAS' => 'auditoria.competencias.index',
            'AVISOS' => 'avisos.index',
            'DASHBOARD' => 'dashboard',
            'HISTORIAL' => 'historial-versiones.index',
        ];
        
        // BUSCAR PRIMERO EN MAYÚSCULAS
        if (isset($rutas[$moduloUpper])) {
            $routeName = $rutas[$moduloUpper];
            if (Route::has($routeName)) {
                return route($routeName);
            }
        }
        
        // BUSCAR EXACTAMENTE
        if (isset($rutas[$modulo])) {
            $routeName = $rutas[$modulo];
            if (Route::has($routeName)) {
                return route($routeName);
            }
        }
        
        // SI ES CARPETA O DOCUMENTO
        if (strpos($moduloUpper, 'FOLDER') !== false || strpos($moduloUpper, 'FOLDERS') !== false) {
            if ($moduloUpper === 'DOCUMENTALFOLDER' && Route::has('documental.index')) {
                return route('documental.index');
            } elseif (Route::has('anexos.index')) {
                return route('anexos.index');
            }
        }
        
        if (strpos($moduloUpper, 'DOCUMENT') !== false || strpos($moduloUpper, 'DOCUMENTS') !== false) {
            if ($moduloUpper === 'DOCUMENTAL_DOCUMENTS' && Route::has('documental.index')) {
                return route('documental.index');
            } elseif (Route::has('anexos.index')) {
                return route('anexos.index');
            }
        }
        
        // FALLBACK 'Alternativa por si falla lo principal': DASHBOARD
        if (Route::has('dashboard')) {
            return route('dashboard');
        }
        
        return null;
    }

    // ========== SCOPES (FILTROS REUTILIZABLES) ==========

    // FILTRA POR MÓDULO
    public function scopeDelModulo($query, $modulo)
    {
        return $query->where('modulo', $modulo);
    }

    // FILTRA POR ACCIÓN
    public function scopeConAccion($query, $accion)
    {
        return $query->where('accion', $accion);
    }

    // FILTRA POR USUARIO
    public function scopeDelUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    // FILTRA ENTRE DOS FECHAS
    public function scopeEntreFechas($query, $inicio, $fin)
    {
        return $query->whereBetween('created_at', [$inicio, $fin]);
    }

    // FILTRA ACTIVIDADES DE HOY
    public function scopeHoy($query)
    {
        return $query->whereDate('created_at', today());
    }

    // FILTRA ACTIVIDADES DE ESTA SEMANA
    public function scopeEstaSemana($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    // FILTRA ACTIVIDADES DE ESTE MES
    public function scopeEsteMes($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    // ========== ACCESORES ==========

    // COLOR SEGÚN LA ACCIÓN
    public function getColorAccionAttribute()
    {
        return match($this->accion) {
            'CREAR' => '#f59e0b',
            'EDITAR' => '#6c757d',
            'ELIMINAR' => '#dc3545',
            'RESTAURAR' => '#10b981',
            'VER' => '#0dcaf0',
            'DESCARGAR' => '#0d6efd',
            'MOVER' => '#6c757d',
            default => '#6b7280'
        };
    }

    // ÍCONO SEGÚN LA ACCIÓN
    public function getIconoAccionAttribute()
    {
        return match($this->accion) {
            'CREAR' => 'bi-plus-circle-fill',
            'EDITAR' => 'bi-pencil-fill',
            'ELIMINAR' => 'bi-trash-fill',
            'RESTAURAR' => 'bi-arrow-counterclockwise',
            'VER' => 'bi-eye-fill',
            'DESCARGAR' => 'bi-download',
            'MOVER' => 'bi-arrow-left-right',
            'VALIDAR' => 'bi-check-circle-fill',
            default => 'bi-clock-history'
        };
    }

    // BADGE HTML DEL NIVEL DE IMPORTANCIA
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

    // FECHA FORMATEADA: DÍA/MES/AÑO HORA:MINUTO:SEGUNDO
    public function getFechaFormateadaAttribute()
    {
        return $this->created_at->format('d/m/Y H:i:s');
    }

    // TIEMPO RELATIVO: "HACE 5 MINUTOS", "HACE 2 DÍAS"
    public function getTiempoRelativoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    // VERIFICA SI UN CAMPO ESPECÍFICO CAMBIÓ
    public function huboCambioEn($campo)
    {
        if (!$this->datos_anteriores || !$this->datos_nuevos) return false;
        $anteriores = is_array($this->datos_anteriores) ? $this->datos_anteriores : json_decode($this->datos_anteriores, true);
        $nuevos = is_array($this->datos_nuevos) ? $this->datos_nuevos : json_decode($this->datos_nuevos, true);
        return isset($nuevos[$campo]) && (!isset($anteriores[$campo]) || $anteriores[$campo] != $nuevos[$campo]);
    }

    // DEVUELVE TODOS LOS CAMPOS QUE CAMBIARON
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
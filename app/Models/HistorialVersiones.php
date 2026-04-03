<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RegistraHistorialVersiones;
use Illuminate\Support\Facades\Route;

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

    /**
     * Mapa de módulos para nombres amigables
     */
    protected static $mapaModulos = [
        // Usuarios y procesos
        'USUARIOS'              => 'Usuarios del Sistema',
        'PROCESOS'              => 'Procesos',
        'DEPARTAMENTOS'         => 'Departamentos',
        
        // Módulos existentes
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
        
        // Formatos originales (para compatibilidad)
        'DocumentalFolder'      => 'Gestión Documental',
        'MatrizFolder'          => 'Carpeta de Matriz',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Obtiene el nombre formateado del módulo
     */
    public function getNombreModuloFormateadoAttribute()
    {
        $modulo = $this->modulo;
        
        // Primero buscamos exactamente como está guardado
        if (isset(self::$mapaModulos[$modulo])) {
            return self::$mapaModulos[$modulo];
        }
        
        // Si no, buscamos en mayúsculas
        $moduloUpper = strtoupper($modulo);
        if (isset(self::$mapaModulos[$moduloUpper])) {
            return self::$mapaModulos[$moduloUpper];
        }
        
        // Si no, devolvemos el original formateado
        return ucfirst(strtolower($modulo));
    }

    /**
     * Obtiene el color del módulo
     */
    public function getColorModuloAttribute()
    {
        $colores = [
            // Usuarios y procesos
            'USUARIOS'             => '#7c3aed',
            'PROCESOS'             => '#7c3aed',
            'DEPARTAMENTOS'        => '#7c3aed',
            
            // Anexos (azul)
            'ANEXOS'               => '#4f46e5',
            'FOLDERS'              => '#4f46e5',
            'DOCUMENTS'            => '#4f46e5',
            
            // Auditorías (plan) (azul)
            'AUDITORIAS'           => '#4f46e5',
            
            // Informes (verde)
            'INFORMES_AUDITORIA'   => '#059669',
            
            // Solicitud de Mejora (rojo)
            'SOLICITUDES_MEJORA'   => '#dc2626',
            
            // Competencias (morado)
            'COMPETENCIAS'         => '#7c3aed',
            
            // Gestión Documental (rojo)
            'GESTION_DOCUMENTAL'   => '#dc2626',
            'DOCUMENTAL_DOCUMENTS' => '#dc2626',
            'DOCUMENTALFOLDER'     => '#dc2626',
            'DocumentalFolder'     => '#dc2626',
            
            // Matriz (morado)
            'MATRIZ'               => '#9333ea',
            'MATRICES_DOCUMENTS'   => '#9333ea',
            'MatrizFolder'         => '#9333ea',
            
            // Lista Maestra (verde)
            'FORMATOS'             => '#16a34a',
            
            // Historial (azul claro)
            'HISTORIAL'            => '#0891b2',
            
            // Notificaciones (naranja)
            'NOTIFICACIONES'       => '#ea580c',
            
            // Avisos (azul)
            'AVISOS'               => '#4fa6e5',
        ];

        // Buscar primero exactamente como está guardado
        if (isset($colores[$this->modulo])) {
            return $colores[$this->modulo];
        }
        
        // Si no, buscar en mayúsculas
        $moduloUpper = strtoupper($this->modulo);
        if (isset($colores[$moduloUpper])) {
            return $colores[$moduloUpper];
        }
        
        // Color por defecto
        return '#737373';
    }

    /**
     * Obtiene el ícono del módulo
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

        // Buscar primero exactamente como está guardado
        if (isset($iconos[$this->modulo])) {
            return $iconos[$this->modulo];
        }
        
        // Si no, buscar en mayúsculas
        $moduloUpper = strtoupper($this->modulo);
        if (isset($iconos[$moduloUpper])) {
            return $iconos[$moduloUpper];
        }
        
        return 'bi-folder';
    }

    /**
     * Obtiene la URL al índice del módulo correspondiente
     *
     * @return string|null
     */
    public function getDetalleUrlAttribute()
    {
        $modulo = $this->modulo;
        $moduloUpper = strtoupper($modulo);
        
        // Mapa de módulos a rutas (en mayúsculas para facilitar búsqueda)
        $rutas = [
            // Usuarios y procesos
            'USUARIOS' => 'admin.usuarios.index',
            'PROCESOS' => 'admin.usuarios.index',
            'DEPARTAMENTOS' => 'admin.usuarios.index',
            
            // Anexos
            'ANEXOS' => 'anexos.index',
            'FOLDERS' => 'anexos.index',
            'DOCUMENTS' => 'anexos.index',
            
            // Gestión Documental
            'DOCUMENTAL' => 'documental.index',
            'DOCUMENTAL_DOCUMENTS' => 'documental.index',
            'DOCUMENTALFOLDER' => 'documental.index',
            
            // Matriz
            'MATRIZ' => 'matriz.index',
            'MATRICES_DOCUMENTS' => 'matriz.index',
            'MATRIXFOLDER' => 'matriz.index',
            
            // Formatos / Lista Maestra
            'FORMATOS' => 'formatos.index',
            
            // Informes de Auditoría
            'INFORMES_AUDITORIA' => 'informes-auditoria.index',
            
            // Auditoría (Plan)
            'AUDITORIAS' => 'auditoria.plan.index',
            
            // Solicitudes de Mejora
            'SOLICITUDES_MEJORA' => 'auditoria.solicitudes.index',
            
            // Competencias
            'COMPETENCIAS' => 'auditoria.competencias.index',
            // Avisos
            'AVISOS' => 'avisos.index',
            // Dashboard (fallback)
            'DASHBOARD' => 'dashboard',
            'HISTORIAL' => 'historial-versiones.index',
        ];
        
        // Buscar en mayúsculas primero
        if (isset($rutas[$moduloUpper])) {
            $routeName = $rutas[$moduloUpper];
            if (Route::has($routeName)) {
                return route($routeName);
            }
        }
        
        // Buscar exactamente como está guardado
        if (isset($rutas[$modulo])) {
            $routeName = $rutas[$modulo];
            if (Route::has($routeName)) {
                return route($routeName);
            }
        }
        
        // Si el módulo es una carpeta o documento de anexos/gestión documental, intentar rutas genéricas
        if (strpos($moduloUpper, 'FOLDER') !== false || strpos($moduloUpper, 'FOLDERS') !== false) {
            // Es una carpeta, ir a anexos o gestión documental según corresponda
            if ($moduloUpper === 'DOCUMENTALFOLDER') {
                if (Route::has('documental.index')) {
                    return route('documental.index');
                }
            } else {
                if (Route::has('anexos.index')) {
                    return route('anexos.index');
                }
            }
        }
        
        if (strpos($moduloUpper, 'DOCUMENT') !== false || strpos($moduloUpper, 'DOCUMENTS') !== false) {
            // Es un documento
            if ($moduloUpper === 'DOCUMENTAL_DOCUMENTS') {
                if (Route::has('documental.index')) {
                    return route('documental.index');
                }
            } else {
                if (Route::has('anexos.index')) {
                    return route('anexos.index');
                }
            }
        }
        
        // Fallback: intentar redirigir al dashboard
        if (Route::has('dashboard')) {
            return route('dashboard');
        }
        
        // Último recurso: devolver null
        return null;
    }

    /**
     * Scope para filtrar por módulo
     */
    public function scopeDelModulo($query, $modulo)
    {
        return $query->where('modulo', $modulo);
    }

    /**
     * Scope para filtrar por acción
     */
    public function scopeConAccion($query, $accion)
    {
        return $query->where('accion', $accion);
    }

    /**
     * Scope para filtrar por usuario
     */
    public function scopeDelUsuario($query, $usuarioId)
    {
        return $query->where('usuario_id', $usuarioId);
    }

    /**
     * Scope para filtrar entre fechas
     */
    public function scopeEntreFechas($query, $inicio, $fin)
    {
        return $query->whereBetween('created_at', [$inicio, $fin]);
    }

    /**
     * Scope para filtrar actividades de hoy
     */
    public function scopeHoy($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope para filtrar actividades de esta semana
     */
    public function scopeEstaSemana($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    /**
     * Scope para filtrar actividades de este mes
     */
    public function scopeEsteMes($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    /**
     * Obtiene el color de la acción
     */
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

    /**
     * Obtiene el ícono de la acción
     */
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

    /**
     * Obtiene el badge de importancia
     */
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

    /**
     * Obtiene la fecha formateada
     */
    public function getFechaFormateadaAttribute()
    {
        return $this->created_at->format('d/m/Y H:i:s');
    }

    /**
     * Obtiene el tiempo relativo
     */
    public function getTiempoRelativoAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Verifica si hubo cambio en un campo específico
     */
    public function huboCambioEn($campo)
    {
        if (!$this->datos_anteriores || !$this->datos_nuevos) return false;
        $anteriores = is_array($this->datos_anteriores) ? $this->datos_anteriores : json_decode($this->datos_anteriores, true);
        $nuevos = is_array($this->datos_nuevos) ? $this->datos_nuevos : json_decode($this->datos_nuevos, true);
        return isset($nuevos[$campo]) && (!isset($anteriores[$campo]) || $anteriores[$campo] != $nuevos[$campo]);
    }

    /**
     * Obtiene los cambios realizados
     */
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
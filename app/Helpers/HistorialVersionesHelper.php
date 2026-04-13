<?php

namespace App\Helpers;

use App\Models\HistorialVersiones;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class HistorialVersionesHelper
{
    /**
     * ESTE ARREGLO CONVIERTE NOMBRES TECNICOS DE MODULOS EN NOMBRES PARA MOSTRAR EN EL HISTORIAL
     * EJEMPLO: 'USUARIOS' SE MUESTRA COMO 'Usuarios', 'FORMATOS' COMO 'Lista Maestra'
     */
    private static $mapaNombresModulos = [
        'USUARIOS' => 'Usuarios',
        'SOLICITUDES_MEJORA' => 'Solicitud de Mejora',
        'MATRICES_DOCUMENTS' => 'Matriz Documental',
        'INFORMES_AUDITORIA' => 'Informes de Auditoría',
        'FORMATOS' => 'Lista Maestra',
        'FOLDERS' => 'Anexos',
        'DOCUMENTAL_DOCUMENTS' => 'Gestión Documental',
        'DOCUMENTALFOLDER' => 'Gestión Documental',
        'DOCUMENTS' => 'Anexos',
        'COMPETENCIAS' => 'Competencias',
        'AUDITORIAS' => 'Plan de Auditoría',
        'HISTORIAL' => 'Historial de Versiones',
        'PROCESOS' => 'Procesos',
        'DEPARTAMENTOS' => 'Departamentos',
        'NOTIFICACIONES' => 'Notificaciones',
        'AVISOS' => 'Avisos',
    ];

    /**
     * LISTA DE MODULOS QUE SE COMPORTAN COMO CARPETAS (CONTENEDORES)
     * ESTOS MODULOS PUEDEN CONTENER OTROS ELEMENTOS DENTRO
     */
    private static $modulosCarpeta = [
        'FOLDERS',
        'DOCUMENTALFOLDER',
        'MatrizFolder',
    ];

    /**
     * LISTA DE MODULOS QUE SE COMPORTAN COMO DOCUMENTOS (ARCHIVOS)
     * ESTOS SON ELEMENTOS QUE SE PUEDEN SUBIR, DESCARGAR, EDITAR
     */
    private static $modulosDocumento = [
        'DOCUMENTS',
        'MATRICES_DOCUMENTS',
        'DOCUMENTAL_DOCUMENTS',
        'FORMATOS',
        'SOLICITUDES_MEJORA',
        'INFORMES_AUDITORIA',
        'AUDITORIAS',
    ];

    /**
     * BANDERA QUE EVITA QUE SE REGISTRE UN HISTORIAL DENTRO DE OTRO HISTORIAL
     * PREVIENE BUCLES INFINITOS Y REGISTROS DUPLICADOS
     */
    private static $registrando = false;

    /**
     * FUNCION QUE TRADUCE EL NOMBRE TECNICO DEL MODULO A UN NOMBRE LEGIBLE 
     * SI NO EXISTE EN EL MAPA, SOLO PONE LA PRIMERA LETRA EN MAYUSCULA
     */
    private static function nombreModulo($modulo)
    {
        return self::$mapaNombresModulos[$modulo] ?? ucfirst(strtolower($modulo));
    }

    /**
     * VERIFICA SI UN ELEMENTO ES UNA CARPETA SEGUN SU MODULO Y TIPO
     * RETORNA TRUE SI EL ELEMENTO ACTUA COMO CARPETA, FALSE SI NO
     */
    private static function esCarpeta($modulo, $elemento)
    {
        if ($modulo === 'COMPETENCIAS' && $elemento) {
            $tipo = null;
            if (is_object($elemento) && isset($elemento->tipo)) {
                $tipo = $elemento->tipo;
            } elseif (is_array($elemento) && isset($elemento['tipo'])) {
                $tipo = $elemento['tipo'];
            }
            return $tipo === 'carpeta';
        }
        
        if ($modulo === 'DOCUMENTALFOLDER') {
            return true;
        }
        
        return in_array($modulo, self::$modulosCarpeta);
    }

    /**
     * VERIFICA SI UN ELEMENTO ES UN DOCUMENTO SEGUN SU MODULO Y TIPO
     * RETORNA TRUE SI EL ELEMENTO ACTUA COMO DOCUMENTO, FALSE SI NO
     */
    private static function esDocumento($modulo, $elemento)
    {
        if ($modulo === 'COMPETENCIAS' && $elemento) {
            $tipo = null;
            if (is_object($elemento) && isset($elemento->tipo)) {
                $tipo = $elemento->tipo;
            } elseif (is_array($elemento) && isset($elemento['tipo'])) {
                $tipo = $elemento['tipo'];
            }
            return $tipo === 'documento';
        }
        
        if ($modulo === 'DOCUMENTAL_DOCUMENTS') {
            return true;
        }
        
        if ($modulo === 'FORMATOS') {
            return true;
        }
        
        return in_array($modulo, self::$modulosDocumento);
    }

    /**
     * EXTRAE EL NOMBRE DEL ELEMENTO BUSCANDO EN DIFERENTES CAMPOS POSIBLES
     * REVISA: titulo, name, nombre, nombre_archivo, archivo_original, etc.
     * SI NO ENCUENTRA NADA, RETORNA NULL
     */
    private static function extraerNombreElemento($elemento)
    {
        if (!$elemento) return null;
        
        if (is_object($elemento) && get_class($elemento) === 'stdClass') {
            $elemento = (array) $elemento;
        }
        
        if (is_object($elemento)) {
            if (isset($elemento->titulo) && !empty($elemento->titulo)) {
                return $elemento->titulo;
            }
            if (isset($elemento->name) && !empty($elemento->name)) {
                return $elemento->name;
            }
            if (isset($elemento->nombre) && !empty($elemento->nombre)) {
                return $elemento->nombre;
            }
            if (isset($elemento->nombre_archivo) && !empty($elemento->nombre_archivo)) {
                return $elemento->nombre_archivo;
            }
            if (isset($elemento->proceso) && isset($elemento->departamento)) {
                return $elemento->departamento . ' (' . $elemento->proceso . ')';
            }
            if (isset($elemento->archivo_original) && !empty($elemento->archivo_original)) {
                return $elemento->archivo_original;
            }
            if (isset($elemento->documento_nombre) && !empty($elemento->documento_nombre)) {
                return $elemento->documento_nombre;
            }
            if (isset($elemento->original_name) && !empty($elemento->original_name)) {
                return $elemento->original_name;
            }
            if (isset($elemento->nombre_auditoria) && !empty($elemento->nombre_auditoria)) {
                return $elemento->nombre_auditoria;
            }
            if (isset($elemento->folio_solicitud) && !empty($elemento->folio_solicitud)) {
                return $elemento->folio_solicitud;
            }
            if (isset($elemento->id)) {
                return '#' . $elemento->id;
            }
        }
        
        if (is_array($elemento)) {
            if (isset($elemento['titulo']) && !empty($elemento['titulo'])) {
                return $elemento['titulo'];
            }
            if (isset($elemento['name']) && !empty($elemento['name'])) {
                return $elemento['name'];
            }
            if (isset($elemento['nombre']) && !empty($elemento['nombre'])) {
                return $elemento['nombre'];
            }
            if (isset($elemento['nombre_archivo']) && !empty($elemento['nombre_archivo'])) {
                return $elemento['nombre_archivo'];
            }
            if (isset($elemento['proceso']) && isset($elemento['departamento'])) {
                return $elemento['departamento'] . ' (' . $elemento['proceso'] . ')';
            }
            if (isset($elemento['archivo_original']) && !empty($elemento['archivo_original'])) {
                return $elemento['archivo_original'];
            }
            if (isset($elemento['documento_nombre']) && !empty($elemento['documento_nombre'])) {
                return $elemento['documento_nombre'];
            }
            if (isset($elemento['original_name']) && !empty($elemento['original_name'])) {
                return $elemento['original_name'];
            }
            if (isset($elemento['nombre_auditoria']) && !empty($elemento['nombre_auditoria'])) {
                return $elemento['nombre_auditoria'];
            }
            if (isset($elemento['folio_solicitud']) && !empty($elemento['folio_solicitud'])) {
                return $elemento['folio_solicitud'];
            }
            if (isset($elemento['id'])) {
                return '#' . $elemento['id'];
            }
        }
        
        return null;
    }

    /**
     * VERIFICA SI YA EXISTE UN REGISTRO MUY RECIENTE PARA EVITAR DUPLICADOS
     * USA LOS ULTIMOS SEGUNDOS ESPECIFICADOS PARA COMPARAR
     */
    private static function yaRegistradoRecientemente($modulo, $accion, $registroId, $segundos = 3)
    {
        $modulosGestionDocumental = ['DOCUMENTALFOLDER', 'DOCUMENTAL_DOCUMENTS'];
        
        if (!in_array($modulo, $modulosGestionDocumental)) {
            return false;
        }
        
        if (!$registroId) {
            return false;
        }
        
        try {
            return HistorialVersiones::where('modulo', $modulo)
                ->where('accion', $accion)
                ->where('registro_id', $registroId)
                ->where('created_at', '>=', now()->subSeconds($segundos))
                ->exists();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * REGISTRA UNA ACCION DE VISUALIZACION EN EL HISTORIAL
     * SE USA CUANDO UN USUARIO VE UN MODULO, DASHBOARD O ELEMENTO ESPECIFICO
     */
    public static function ver($modulo, $elemento = null, $contexto = null)
    {
        if (self::$registrando) return null;
        self::$registrando = true;
        
        try {
            $nombreModulo = self::nombreModulo($modulo);
            
            if ($contexto === 'dashboard') {
                $descripcion = "Accedió al Dashboard del Sistema";
            } elseif ($elemento) {
                $nombre = self::extraerNombreElemento($elemento) ?: 'desconocido';
                $descripcion = "Visualizó '{$nombre}' en {$nombreModulo}";
            } else {
                $descripcion = "Accedió al módulo de {$nombreModulo}";
            }
            
            $resultado = self::registrar($modulo, 'VER', $descripcion, $elemento);
            self::$registrando = false;
            return $resultado;
        } catch (\Exception $e) {
            self::$registrando = false;
            throw $e;
        }
    }

    /**
     * REGISTRA UNA ACCION DE CREACION EN EL HISTORIAL
     * SE USA CUANDO SE CREA ALGO NUEVO: USUARIO, CARPETA, DOCUMENTO, PROCESO, ETC
     */
    public static function crear($modulo, $elemento, $datos = [])
    {
        if (self::$registrando || $modulo === 'HISTORIAL') return null;
        self::$registrando = true;
        
        try {
            $nombreModulo = self::nombreModulo($modulo);
            $nombre = self::extraerNombreElemento($elemento) ?: 'desconocido';
            
            if (self::esCarpeta($modulo, $elemento)) {
                $descripcion = "Se creó la carpeta '{$nombre}' en {$nombreModulo}";
            } elseif (self::esDocumento($modulo, $elemento)) {
                $descripcion = "Se subió el documento '{$nombre}' en {$nombreModulo}";
            } elseif ($modulo === 'USUARIOS') {
                $descripcion = "Se creó el usuario '{$nombre}' en {$nombreModulo}";
            } elseif ($modulo === 'PROCESOS') {
                $descripcion = "Se creó el proceso '{$nombre}' en {$nombreModulo}";
            } elseif ($modulo === 'DEPARTAMENTOS') {
                $descripcion = "Se creó el departamento '{$nombre}' en {$nombreModulo}";
            } elseif ($modulo === 'SOLICITUDES_MEJORA') {
                $descripcion = "Se creó la solicitud de mejora '{$nombre}' en {$nombreModulo}";
            } elseif ($modulo === 'FORMATOS') {
                $descripcion = "Se agregó el documento '{$nombre}' a la Lista Maestra";
            } elseif ($modulo === 'AVISOS') {
                $descripcion = "Se creó el aviso '{$nombre}' en {$nombreModulo}";
            } else {
                $descripcion = "Se creó '{$nombre}' en {$nombreModulo}";
            }
            
            $resultado = self::registrar($modulo, 'CREAR', $descripcion, $elemento, $datos);
            self::$registrando = false;
            return $resultado;
        } catch (\Exception $e) {
            self::$registrando = false;
            throw $e;
        }
    }

    /**
     * REGISTRA UNA ACCION DE SUBIR DOCUMENTO EN EL HISTORIAL
     * SE USA CUANDO UN USUARIO SUBE UN ARCHIVO AL SISTEMA
     */
    public static function subir($modulo, $elemento, $datos = [], $esEnvio = false)
    {
        if (self::$registrando || $modulo === 'HISTORIAL') return null;
        self::$registrando = true;
        
        try {
            $nombreModulo = self::nombreModulo($modulo);
            $nombre = self::extraerNombreElemento($elemento) ?: 'desconocido';
            
            if ($modulo === 'FORMATOS' && $esEnvio) {
                $descripcion = "Se envió el documento '{$nombre}' a la Lista Maestra";
            } elseif ($modulo === 'FORMATOS') {
                $descripcion = "Se subió el documento '{$nombre}' a la Lista Maestra";
            } else {
                $descripcion = "Se subió el documento '{$nombre}' en {$nombreModulo}";
            }
            
            $resultado = self::registrar($modulo, 'SUBIR', $descripcion, $elemento, $datos);
            self::$registrando = false;
            return $resultado;
        } catch (\Exception $e) {
            self::$registrando = false;
            throw $e;
        }
    }

    /**
     * REGISTRA UNA ACCION DE EDICION EN EL HISTORIAL
     * COMPARA DATOS ANTERIORES VS NUEVOS Y GENERA UNA DESCRIPCION CLARA DEL CAMBIO
     * DETECTA RENOMBRES, CAMBIOS DE ESTADO, ETC
     */
    public static function editar($modulo, $elemento, $datosAnteriores, $datosNuevos)
    {
        if (self::$registrando || $modulo === 'HISTORIAL') return null;
        self::$registrando = true;
        
        try {
            $nombreModulo = self::nombreModulo($modulo);
            $nombre = self::extraerNombreElemento($elemento) ?: 'desconocido';
            
            $nombreAnterior = 
                $datosAnteriores['titulo'] ??
                $datosAnteriores['name'] ?? 
                $datosAnteriores['nombre'] ?? 
                $datosAnteriores['nombre_archivo'] ?? 
                $datosAnteriores['archivo_original'] ?? 
                $datosAnteriores['documento_nombre'] ?? 
                $datosAnteriores['archivo_nombre'] ?? 
                $datosAnteriores['original_name'] ?? 
                $datosAnteriores['nombre_auditoria'] ?? 
                $datosAnteriores['proceso'] ?? 
                $datosAnteriores['departamento'] ?? 
                $datosAnteriores['folio_solicitud'] ??
                'desconocido';
            
            $nombreNuevo = 
                $datosNuevos['titulo'] ??
                $datosNuevos['name'] ?? 
                $datosNuevos['nombre'] ?? 
                $datosNuevos['nombre_archivo'] ?? 
                $datosNuevos['archivo_original'] ?? 
                $datosNuevos['documento_nombre'] ?? 
                $datosNuevos['archivo_nombre'] ?? 
                $datosNuevos['original_name'] ?? 
                $datosNuevos['nombre_auditoria'] ?? 
                $datosNuevos['proceso'] ?? 
                $datosNuevos['departamento'] ?? 
                $datosNuevos['folio_solicitud'] ??
                $nombre;
            
            if ($modulo === 'USUARIOS') {
                $estadoAnterior = $datosAnteriores['is_active'] ?? null;
                $estadoNuevo = $datosNuevos['is_active'] ?? null;
                
                if ($estadoAnterior !== $estadoNuevo) {
                    $estadoTexto = $estadoNuevo ? 'activó' : 'desactivó';
                    $descripcion = "Se {$estadoTexto} el usuario '{$nombre}' en {$nombreModulo}";
                } elseif ($nombreAnterior !== $nombreNuevo) {
                    $descripcion = "Se renombró el usuario de '{$nombreAnterior}' a '{$nombreNuevo}' en {$nombreModulo}";
                } else {
                    $descripcion = "Se editó el usuario '{$nombre}' en {$nombreModulo}";
                }
            }
            elseif ($modulo === 'AVISOS') {
                if ($nombreAnterior !== $nombreNuevo) {
                    $descripcion = "Se renombró el aviso de '{$nombreAnterior}' a '{$nombreNuevo}' en {$nombreModulo}";
                } else {
                    $descripcion = "Se editó el aviso '{$nombre}' en {$nombreModulo}";
                }
            }
            elseif (self::esCarpeta($modulo, $elemento)) {
                if ($nombreAnterior !== $nombreNuevo) {
                    $descripcion = "Se renombró carpeta de '{$nombreAnterior}' a '{$nombreNuevo}' en {$nombreModulo}";
                } else {
                    $descripcion = "Se editó la carpeta '{$nombre}' en {$nombreModulo}";
                }
            } elseif (self::esDocumento($modulo, $elemento)) {
                if ($nombreAnterior !== $nombreNuevo) {
                    $descripcion = "Se renombró documento de '{$nombreAnterior}' a '{$nombreNuevo}' en {$nombreModulo}";
                } else {
                    $descripcion = "Se editó el documento '{$nombre}' en {$nombreModulo}";
                }
            } elseif ($modulo === 'PROCESOS') {
                if ($nombreAnterior !== $nombreNuevo) {
                    $descripcion = "Se renombró el proceso de '{$nombreAnterior}' a '{$nombreNuevo}' en {$nombreModulo}";
                } else {
                    $descripcion = "Se editó el proceso '{$nombre}' en {$nombreModulo}";
                }
            } elseif ($modulo === 'DEPARTAMENTOS') {
                $proceso = $datosAnteriores['proceso'] ?? $datosNuevos['proceso'] ?? '';
                if ($nombreAnterior !== $nombreNuevo) {
                    $descripcion = "Se renombró el departamento de '{$nombreAnterior}' a '{$nombreNuevo}' en el proceso '{$proceso}' en {$nombreModulo}";
                } else {
                    $descripcion = "Se editó el departamento '{$nombre}' del proceso '{$proceso}' en {$nombreModulo}";
                }
            } elseif ($modulo === 'SOLICITUDES_MEJORA') {
                if ($nombreAnterior !== $nombreNuevo) {
                    $descripcion = "Se editó la solicitud de mejora de '{$nombreAnterior}' a '{$nombreNuevo}' en {$nombreModulo}";
                } else {
                    $descripcion = "Se editó la solicitud de mejora '{$nombre}' en {$nombreModulo}";
                }
            } elseif ($modulo === 'FORMATOS') {
                if ($nombreAnterior !== $nombreNuevo) {
                    $descripcion = "Se editó el documento de '{$nombreAnterior}' a '{$nombreNuevo}' en la Lista Maestra";
                } else {
                    $descripcion = "Se editó el documento '{$nombre}' en la Lista Maestra";
                }
            } else {
                if ($nombreAnterior !== $nombreNuevo) {
                    $descripcion = "Se renombró de '{$nombreAnterior}' a '{$nombreNuevo}' en {$nombreModulo}";
                } else {
                    $descripcion = "Se editó '{$nombre}' en {$nombreModulo}";
                }
            }
            
            $resultado = self::registrar($modulo, 'EDITAR', $descripcion, $elemento, $datosNuevos, $datosAnteriores);
            self::$registrando = false;
            return $resultado;
        } catch (\Exception $e) {
            self::$registrando = false;
            throw $e;
        }
    }

    /**
     * REGISTRA UNA ACCION DE ELIMINACION EN EL HISTORIAL
     * GUARDA LOS DATOS DEL ELEMENTO ELIMINADO PARA POSIBLE RESTAURACION FUTURA
     */
    public static function eliminar($modulo, $elemento, $datos = [])
    {
        if (self::$registrando || $modulo === 'HISTORIAL') return null;
        self::$registrando = true;
        
        try {
            $nombreModulo = self::nombreModulo($modulo);
            $nombre = self::extraerNombreElemento($elemento) ?: 'desconocido';
            
            if (self::esCarpeta($modulo, $elemento)) {
                $descripcion = "Se eliminó la carpeta '{$nombre}' en {$nombreModulo}";
            } elseif (self::esDocumento($modulo, $elemento)) {
                $descripcion = "Se eliminó el documento '{$nombre}' en {$nombreModulo}";
            } elseif ($modulo === 'USUARIOS') {
                $descripcion = "Se eliminó el usuario '{$nombre}' en {$nombreModulo}";
            } elseif ($modulo === 'PROCESOS') {
                $descripcion = "Se eliminó el proceso '{$nombre}' en {$nombreModulo}";
            } elseif ($modulo === 'DEPARTAMENTOS') {
                $proceso = $datos['proceso'] ?? (is_object($elemento) ? $elemento->proceso ?? '' : '');
                $descripcion = "Se eliminó el departamento '{$nombre}' del proceso '{$proceso}' en {$nombreModulo}";
            } elseif ($modulo === 'SOLICITUDES_MEJORA') {
                $descripcion = "Se eliminó la solicitud de mejora '{$nombre}' en {$nombreModulo}";
            } elseif ($modulo === 'FORMATOS') {
                $descripcion = "Se eliminó el documento '{$nombre}' de la Lista Maestra";
            } elseif ($modulo === 'AVISOS') {
                $descripcion = "Se eliminó el aviso '{$nombre}' en {$nombreModulo}";
            } else {
                $descripcion = "Se eliminó '{$nombre}' en {$nombreModulo}";
            }
            
            $datosParaGuardar = $datos;
            
            if (empty($datosParaGuardar) && $elemento) {
                if (is_object($elemento)) {
                    if (method_exists($elemento, 'toArray')) {
                        $datosParaGuardar = $elemento->toArray();
                    } else {
                        $datosParaGuardar = (array) $elemento;
                    }
                } elseif (is_array($elemento)) {
                    $datosParaGuardar = $elemento;
                }
            }
            
            $resultado = self::registrar($modulo, 'ELIMINAR', $descripcion, $elemento, [], $datosParaGuardar);
            self::$registrando = false;
            return $resultado;
        } catch (\Exception $e) {
            self::$registrando = false;
            throw $e;
        }
    }

    /**
     * REGISTRA UNA ACCION DE RESTAURACION EN EL HISTORIAL
     * SE USA CUANDO SE RECUPERA UN ELEMENTO QUE HABIA SIDO ELIMINADO
     * NOTA: YA NO EXCLUYE EL MODULO FORMATOS, AHORA SI REGISTRA ESA ACCION
     */
    public static function restaurar($modulo, $elemento, $datos = [])
    {
        if (self::$registrando) {
            return null;
        }
        
        self::$registrando = true;
        
        try {
            $nombreModulo = self::nombreModulo($modulo);
            $nombre = self::extraerNombreElemento($elemento) ?: 'desconocido';
            
            if (self::esCarpeta($modulo, $elemento)) {
                $descripcion = "Se restauró la carpeta '{$nombre}' en {$nombreModulo}";
            } elseif (self::esDocumento($modulo, $elemento)) {
                $descripcion = "Se restauró el documento '{$nombre}' en {$nombreModulo}";
            } elseif ($modulo === 'USUARIOS') {
                $descripcion = "Se restauró el usuario '{$nombre}' en {$nombreModulo}";
            } elseif ($modulo === 'PROCESOS') {
                $descripcion = "Se restauró el proceso '{$nombre}' en {$nombreModulo}";
            } elseif ($modulo === 'DEPARTAMENTOS') {
                $proceso = $datos['proceso'] ?? (is_object($elemento) ? $elemento->proceso ?? '' : '');
                $descripcion = "Se restauró el departamento '{$nombre}' del proceso '{$proceso}' en {$nombreModulo}";
            } elseif ($modulo === 'SOLICITUDES_MEJORA') {
                $descripcion = "Se restauró la solicitud de mejora '{$nombre}' en {$nombreModulo}";
            } elseif ($modulo === 'FORMATOS') {
                $descripcion = "Se restauró el documento '{$nombre}' en la Lista Maestra";
            } elseif ($modulo === 'AVISOS') {
                $descripcion = "Se restauró el aviso '{$nombre}' en {$nombreModulo}";
            } else {
                $descripcion = "Se restauró '{$nombre}' en {$nombreModulo}";
            }
            
            $resultado = self::registrar($modulo, 'RESTAURAR', $descripcion, $elemento, $datos);
            self::$registrando = false;
            return $resultado;
        } catch (\Exception $e) {
            self::$registrando = false;
            throw $e;
        }
    }

    /**
     * REGISTRA UNA ACCION DE MOVIMIENTO EN EL HISTORIAL
     * REGISTRA CUANDO UN ELEMENTO (CARPETA O DOCUMENTO) CAMBIA DE UBICACION
     * GUARDA EL ORIGEN Y EL DESTINO DEL MOVIMIENTO
     */
    public static function mover($modulo, $elemento, $origen, $destino)
    {
        if (self::$registrando || $modulo === 'HISTORIAL') return null;
        self::$registrando = true;
        
        try {
            $nombreModulo = self::nombreModulo($modulo);
            $nombre = self::extraerNombreElemento($elemento) ?: 'desconocido';
            
            $nombreOrigen = $origen ? ($origen->name ?? $origen->nombre ?? 'Raíz') : 'Raíz';
            $nombreDestino = $destino ? ($destino->name ?? $destino->nombre ?? 'Raíz') : 'Raíz';
            
            if (self::esCarpeta($modulo, $elemento)) {
                $descripcion = "Se movió la carpeta '{$nombre}' de '{$nombreOrigen}' a '{$nombreDestino}' en {$nombreModulo}";
            } elseif (self::esDocumento($modulo, $elemento)) {
                $descripcion = "Se movió el documento '{$nombre}' de '{$nombreOrigen}' a '{$nombreDestino}' en {$nombreModulo}";
            } else {
                $descripcion = "Se movió '{$nombre}' de '{$nombreOrigen}' a '{$nombreDestino}' en {$nombreModulo}";
            }
            
            $resultado = self::registrar($modulo, 'MOVER', $descripcion, $elemento);
            self::$registrando = false;
            return $resultado;
        } catch (\Exception $e) {
            self::$registrando = false;
            throw $e;
        }
    }

    /**
     * REGISTRA UNA ACCION DE DESCARGA EN EL HISTORIAL
     * SE USA CUANDO UN USUARIO DESCARGA UN ARCHIVO DEL SISTEMA
     */
    public static function descargar($modulo, $elemento)
    {
        if (self::$registrando) return null;
        self::$registrando = true;
        
        try {
            $nombreModulo = self::nombreModulo($modulo);
            $nombre = self::extraerNombreElemento($elemento) ?: 'desconocido';
            
            if ($modulo === 'FORMATOS') {
                $descripcion = "Se descargó el documento '{$nombre}' de la Lista Maestra";
            } elseif ($modulo === 'AVISOS') {
                $descripcion = "Se descargó el documento '{$nombre}' del aviso en {$nombreModulo}";
            } else {
                $descripcion = "Se descargó el documento '{$nombre}' en {$nombreModulo}";
            }
            
            $resultado = self::registrar($modulo, 'DESCARGAR', $descripcion, $elemento);
            self::$registrando = false;
            return $resultado;
        } catch (\Exception $e) {
            self::$registrando = false;
            throw $e;
        }
    }

    /**
     * METODO PRINCIPAL QUE GUARDA EL REGISTRO EN LA BASE DE DATOS
     * RECIBE TODOS LOS DATOS, OBTIENE LA INFORMACION DEL USUARIO, IP, NAVEGADOR
     * Y CREA UN NUEVO REGISTRO EN LA TABLA historial_versiones
     */
    private static function registrar($modulo, $accion, $descripcion, $elemento = null, $datosNuevos = [], $datosAnteriores = [])
    {
        $user = Auth::user();
        
        $nivelImportancia = match($accion) {
            'ELIMINAR' => 'alto',
            'RESTAURAR' => 'alto',
            'EDITAR' => 'normal',
            'CREAR' => 'normal',
            'SUBIR' => 'normal',
            'MOVER' => 'normal',
            default => 'bajo'
        };
        
        $datos = [
            'modulo' => $modulo,
            'accion' => $accion,
            'descripcion' => $descripcion,
            'nivel_importancia' => $nivelImportancia,
            'datos_anteriores' => !empty($datosAnteriores) ? json_encode($datosAnteriores) : null,
            'datos_nuevos' => !empty($datosNuevos) ? json_encode($datosNuevos) : null,
            'tabla_afectada' => $elemento && method_exists($elemento, 'getTable') ? $elemento->getTable() : strtolower($modulo),
            'registro_id' => $elemento->id ?? null,
            'elemento_nombre' => self::extraerNombreElemento($elemento),
        ];
        
        if ($modulo === 'HISTORIAL') {
            $datos['datos_anteriores'] = null;
            $datos['datos_nuevos'] = null;
        }
        
        return HistorialVersiones::create([
            'usuario_nombre' => $user->name ?? 'Sistema',
            'usuario_id' => $user->id ?? null,
            'usuario_email' => $user->email ?? null,
            'usuario_rol' => $user->role ?? 'sistema',
            'modulo' => $datos['modulo'],
            'accion' => $datos['accion'],
            'descripcion' => $datos['descripcion'],
            'nivel_importancia' => $datos['nivel_importancia'],
            'datos_anteriores' => $datos['datos_anteriores'],
            'datos_nuevos' => $datos['datos_nuevos'],
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'tabla_afectada' => $datos['tabla_afectada'],
            'registro_id' => $datos['registro_id'],
            'elemento_nombre' => $datos['elemento_nombre'],
        ]);
    }
}
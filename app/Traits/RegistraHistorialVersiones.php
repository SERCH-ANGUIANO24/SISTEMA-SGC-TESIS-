<?php

namespace App\Traits;

use App\Helpers\HistorialVersionesHelper;

/**
 * TRAIT QUE SE ENCARGA DE REGISTRAR AUTOMATICAMENTE LAS ACCIONES EN EL HISTORIAL
 * AL SER USADO EN UN MODELO, CAPTURA LOS EVENTOS DE ELOQUENT (CREAR, EDITAR, ELIMINAR, RESTAURAR)
 * Y LOS REGISTRA USANDO EL HistorialVersionesHelper
 */
trait RegistraHistorialVersiones
{
    /**
     * METODO PRINCIPAL QUE REGISTRA LOS EVENTOS DEL MODELO
     * SE EJECUTA AUTOMATICAMENTE CUANDO EL TRAIT ES USADO EN UN MODELO
     * AQUI SE DEFINEN LOS "HOOKS"-"función para que se ejecute automáticamente cuando algo específico suceda" PARA CREAR, ACTUALIZAR, ELIMINAR Y RESTAURAR
     */
    protected static function bootRegistraHistorialVersiones()
    {
        /**
         * EVENTO QUE SE DISPARA CUANDO SE CREA UN NUEVO REGISTRO EN LA BASE DE DATOS
         * CAPTURA LA CREACION DE CUALQUIER MODELO QUE USE ESTE TRAIT
         */
        static::created(function ($model) {
            if (self::debeRegistrar('crear', $model)) {
                $modulo = self::getNombreModulo($model);
                
                // EXCLUYE EL MODULO DE GESTION DOCUMENTAL PARA EVITAR REGISTROS DUPLICADOS
                if ($modulo === 'DOCUMENTAL_DOCUMENTS') {
                    return;
                }
                
                // ELIMINADA la exclusión de FORMATOS para CREAR
                // if ($modulo === 'FORMATOS') { return; }
                
                HistorialVersionesHelper::crear($modulo, $model);
            }
        });

        /**
         * EVENTO QUE SE DISPARA CUANDO SE ACTUALIZA UN REGISTRO EXISTENTE
         * COMPARA LOS DATOS VIEJOS VS NUEVOS Y REGISTRA LOS CAMBIOS
         * DETECTA RENOMBRES, CAMBIOS DE ESTADO, MODIFICACIONES EN GENERAL
         */
        static::updated(function ($model) {
            // SI EL MODELO FUE RESTAURADO RECIENTEMENTE, NO REGISTRAR COMO EDICION
            if ($model->wasRecentlyRestored) {
                return;
            }
            
            // OBTIENE EL ESTADO ANTERIOR Y ACTUAL DEL CAMPO 'deleted_at' (SOFT DELETE)
            $originalDeletedAt = $model->getOriginal('deleted_at');
            $currentDeletedAt = $model->deleted_at;
            
            // SI EL REGISTRO FUE RESTAURADO (PASO DE ELIMINADO A ACTIVO), NO REGISTRAR COMO EDICION
            if (!is_null($originalDeletedAt) && is_null($currentDeletedAt)) {
                return;
            }
            
            // VERIFICA SI HUBO CAMBIOS Y SI DEBE REGISTRAR LA EDICION
            if (self::debeRegistrar('editar', $model) && $model->wasChanged()) {
                $modulo = self::getNombreModulo($model);
                $datosAnteriores = $model->getOriginal();  // DATOS ANTES DE LA MODIFICACION
                $datosNuevos = $model->getAttributes();    // DATOS DESPUES DE LA MODIFICACION

                HistorialVersionesHelper::editar($modulo, $model, $datosAnteriores, $datosNuevos);
            }
        });

        /**
         * EVENTO QUE SE DISPARA CUANDO SE ELIMINA UN REGISTRO
         * GUARDA LOS DATOS DEL ELEMENTO ELIMINADO PARA POSIBLE RESTAURACION FUTURA
         * TAMBIEN CAPTURA SOFT DELETES (CUANDO SE USA deleted_at)
         */
        static::deleted(function ($model) {
            if (self::debeRegistrar('eliminar', $model)) {
                $modulo = self::getNombreModulo($model);
                HistorialVersionesHelper::eliminar($modulo, $model);
            }
        });

        /**
         * EVENTO QUE SE DISPARA CUANDO SE RESTAURA UN REGISTRO ELIMINADO (SOFT DELETE)
         * ESTE EVENTO SOLO EXISTE SI EL MODELO USA EL TRAIT SoftDeletes
         * REGISTRA LA ACCION DE RESTAURACION EN EL HISTORIAL
         */
        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                $modulo = self::getNombreModulo($model);
                
                // ELIMINADA la exclusión de FORMATOS para RESTAURAR - AHORA SI REGISTRA
                // if ($modulo === 'FORMATOS') { return; }
                
                // EXCLUYE MODULOS DE GESTION DOCUMENTAL PARA EVITAR REGISTROS DUPLICADOS
                if ($modulo === 'DOCUMENTAL_DOCUMENTS') {
                    return;
                }
                
                if ($modulo === 'DOCUMENTALFOLDER') {
                    return;
                }
                
                if ($modulo === 'SOLICITUDES_MEJORA') {
                    return;
                }
                
                // VERIFICA SI DEBE REGISTRAR Y SI NO ES UNA RESTAURACION MASIVA
                if (self::debeRegistrar('restaurar', $model) && !self::isBulkRestoring()) {
                    $tabla = $model->getTable();
                    // SOLO EXCLUIMOS SI ES UNA RESTAURACION MASIVA (BULK RESTORING) EN FORMATOS
                    if ($tabla === 'formatos' && self::isBulkRestoring()) {
                        return;
                    }
                    HistorialVersionesHelper::restaurar($modulo, $model);
                }
            });
        }
    }

    /**
     * BANDERA ESTATICA QUE INDICA SI SE ESTA REALIZANDO UNA RESTAURACION MASIVA
     * SE USA PARA EVITAR REGISTROS DUPLICADOS CUANDO SE RESTAURAN MULTIPLES REGISTROS A LA VEZ
     */
    protected static $bulkRestoring = false;

    /**
     * VERIFICA SI ACTUALMENTE SE ESTA EJECUTANDO UNA RESTAURACION MASIVA
     * RETORNA TRUE SI ES BULK RESTORING, FALSE EN CASO CONTRARIO
     */
    public static function isBulkRestoring()
    {
        return self::$bulkRestoring;
    }

    /**
     * ESTABLECE EL ESTADO DE LA BANDERA DE RESTAURACION MASIVA
     * SE USA PARA ACTIVAR/DESACTIVAR EL MODO BULK RESTORING
     */
    public static function setBulkRestoring($value)
    {
        self::$bulkRestoring = $value;
    }

    /**
     * CONVIERTE EL NOMBRE DE LA TABLA DEL MODELO EN UN CODIGO DE MODULO LEGIBLE
     * USA UN MAPA DE TABLAS A CODIGOS DE MODULO QUE USA EL HistorialVersionesHelper
     * SI LA TABLA NO ESTA EN EL MAPA, CONVIERTE EL NOMBRE A MAYUSCULAS
     */
    protected static function getNombreModulo($model)
    {
        $tabla = $model->getTable();
        $mapa = [
            'anexos' => 'ANEXOS',
            'folders' => 'FOLDERS',
            'documents' => 'DOCUMENTS',
            'documental_documents' => 'DOCUMENTAL_DOCUMENTS',
            'documental_folders' => 'DOCUMENTALFOLDER',
            'matriz' => 'MATRIZ',
            'matriz_folders' => 'MatrizFolder',
            'matriz_documents' => 'MATRICES_DOCUMENTS',
            'formatos' => 'FORMATOS',
            'auditorias' => 'AUDITORIAS',
            'solicitudes_mejora' => 'SOLICITUDES_MEJORA',
            'competencias' => 'COMPETENCIAS',
            'informes_auditoria' => 'INFORMES_AUDITORIA',
            'users' => 'USUARIOS',
            'procesos_custom' => 'PROCESOS',
            'procesos_departamentos' => 'DEPARTAMENTOS',
            'notificaciones' => 'NOTIFICACIONES',
            'avisos' => 'AVISOS',
            'historial_versiones' => 'HISTORIAL',
            'audits' => 'AUDITORIAS',
        ];
        return $mapa[$tabla] ?? strtoupper($tabla);
    }

    /**
     * VERIFICA SI SE DEBE REGISTRAR UNA ACCION EN EL HISTORIAL
     * EXCLUYE TABLAS QUE NO DEBEN SER REGISTRADAS (HISTORIAL, SESIONES, ETC)
     * RETORNA TRUE SI DEBE REGISTRAR, FALSE SI NO
     */
    protected static function debeRegistrar($accion, $model)
    {
        // LISTA DE TABLAS QUE NUNCA DEBEN REGISTRARSE EN EL HISTORIAL
        $excluir = [
            'historial_versiones',      // PARA EVITAR REGISTROS INFINITOS
            'sessions',                  // SESIONES DE USUARIO
            'password_resets',           // RESETEOS DE CONTRASEÑA
            'failed_jobs',               // TRABAJOS FALLIDOS
            'personal_access_tokens',    // TOKENS DE ACCESO PERSONAL
        ];
        
        $tabla = $model->getTable();
        
        // SI LA TABLA ESTA EN LA LISTA DE EXCLUIDAS, NO REGISTRAR
        if (in_array($tabla, $excluir)) {
            return false;
        }
        
        // ELIMINADA la exclusión de FORMATOS para RESTAURAR - AHORA SI REGISTRA
        // if ($accion === 'restaurar' && $tabla === 'formatos') {
        //     return false;
        // }
        
        return true;
    } 
}
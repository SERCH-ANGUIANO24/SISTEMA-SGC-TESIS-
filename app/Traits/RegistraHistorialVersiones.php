<?php

namespace App\Traits;

use App\Helpers\HistorialVersionesHelper;

trait RegistraHistorialVersiones
{
    protected static function bootRegistraHistorialVersiones()
    {
        static::created(function ($model) {
            if (self::debeRegistrar('crear', $model)) {
                $modulo = self::getNombreModulo($model);
                
                if ($modulo === 'DOCUMENTAL_DOCUMENTS') {
                    return;
                }
                
                if ($modulo === 'FORMATOS') {
                    return;
                }
                
                HistorialVersionesHelper::crear($modulo, $model);
            }
        });

        static::updated(function ($model) {
            if (self::debeRegistrar('editar', $model) && $model->wasChanged()) {
                $modulo = self::getNombreModulo($model);
                $datosAnteriores = $model->getOriginal();
                $datosNuevos = $model->getAttributes();

                HistorialVersionesHelper::editar($modulo, $model, $datosAnteriores, $datosNuevos);
            }
        });

        static::deleted(function ($model) {
            if (self::debeRegistrar('eliminar', $model)) {
                $modulo = self::getNombreModulo($model);
                HistorialVersionesHelper::eliminar($modulo, $model);
            }
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                $modulo = self::getNombreModulo($model);
                
                // ✅ EXCLUIR FORMATOS COMPLETAMENTE - NO REGISTRAR NADA
                if ($modulo === 'FORMATOS') {
                    return;
                }
                
                // ✅ EXCLUIR DOCUMENTAL_DOCUMENTS
                if ($modulo === 'DOCUMENTAL_DOCUMENTS') {
                    return;
                }
                
                // ✅ NO REGISTRAR SI ES BULK RESTORING O SI ES FORMATOS
                if (self::debeRegistrar('restaurar', $model) && !self::isBulkRestoring()) {
                    // Verificar nuevamente que no sea FORMATOS
                    $tabla = $model->getTable();
                    if ($tabla === 'formatos') {
                        return;
                    }
                    HistorialVersionesHelper::restaurar($modulo, $model);
                }
            });
        }
    }

    protected static $bulkRestoring = false;

    public static function isBulkRestoring()
    {
        return self::$bulkRestoring;
    }

    public static function setBulkRestoring($value)
    {
        self::$bulkRestoring = $value;
    }

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

    protected static function debeRegistrar($accion, $model)
    {
        $excluir = [
            'historial_versiones',
            'sessions',
            'password_resets',
            'failed_jobs',
            'personal_access_tokens',
        ];
        
        $tabla = $model->getTable();
        
        if (in_array($tabla, $excluir)) {
            return false;
        }
        
        // ✅ EXCLUIR FORMATOS PARA CUALQUIER ACCIÓN DE RESTAURACIÓN
        if ($accion === 'restaurar' && $tabla === 'formatos') {
            return false;
        }
        
        return true;
    } 
}
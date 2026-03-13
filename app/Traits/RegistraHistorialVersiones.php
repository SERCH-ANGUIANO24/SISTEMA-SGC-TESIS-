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
                HistorialVersionesHelper::crear($modulo, $model);
            }
        });

        static::updated(function ($model) {
            if (self::debeRegistrar('editar', $model) && $model->wasChanged()) {
                $modulo = self::getNombreModulo($model);
                $cambios = $model->getChanges();
                HistorialVersionesHelper::editar($modulo, $model, $cambios);
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
                if (self::debeRegistrar('restaurar', $model)) {
                    $modulo = self::getNombreModulo($model);
                    HistorialVersionesHelper::restaurar($modulo, $model);
                }
            });
        }
    }

    protected static function getNombreModulo($model)
    {
        $tabla = $model->getTable();
        $mapa = [
            'anexos' => 'ANEXOS',
            'auditorias' => 'AUDITORIAS',
            'audits' => 'AUDITORIAS',
            'documentos' => 'GESTION_DOCUMENTAL',
            'gestion_documental' => 'GESTION_DOCUMENTAL',
            'matriz' => 'MATRIZ',
            'matriz_procesos' => 'MATRIZ',
            'formatos' => 'FORMATOS',
            'users' => 'USUARIOS',
            'notificaciones' => 'NOTIFICACIONES',
            'avisos' => 'AVISOS',
            'historial_versiones' => 'HISTORIAL'
        ];
        return $mapa[$tabla] ?? strtoupper($tabla);
    }

    protected static function debeRegistrar($accion, $model)
    {
        return true;
    }
}
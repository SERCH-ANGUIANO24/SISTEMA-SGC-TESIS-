<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * MODELO PARA PROCESOS Y DEPARTAMENTOS PERSONALIZADOS
 * GUARDA PROCESOS CON SUS DEPARTAMENTOS ASOCIADOS (RELACIÓN UNO A MUCHOS)
 * SE USA EN MÓDULOS COMO FORMATOS, GESTIÓN DOCUMENTAL, ETC
 */
class ProcesoCustom extends Model
{
    // NOMBRE DE LA TABLA EN BASE DE DATOS
    protected $table = 'procesos_custom';

    // CAMPOS QUE SE PUEDEN LLENAR DE FORMA MASIVA
    protected $fillable = [
        'proceso',      // NOMBRE DEL PROCESO (EJ: PLANEACION, INSCRIPCION, ETC)
        'departamento', // DEPARTAMENTO QUE PERTENECE A ESE PROCESO
    ];
}
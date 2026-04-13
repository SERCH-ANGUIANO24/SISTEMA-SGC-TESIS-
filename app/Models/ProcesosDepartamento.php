<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * MODELO PARA LA RELACIÓN PROCESO - DEPARTAMENTO
 * PERMITE ASIGNAR DEPARTAMENTOS A PROCESOS DE FORMA PERSONALIZADA
 * USA EL MAPA DEL MODELO FORMATO COMO BASE Y AGREGA REGISTROS ADICIONALES
 */
class ProcesosDepartamento extends Model
{
    // NOMBRE DE LA TABLA EN BASE DE DATOS
    protected $table = 'procesos_departamentos';

    // CAMPOS QUE SE PUEDEN LLENAR DE FORMA MASIVA
    protected $fillable = [
        'proceso',      // NOMBRE DEL PROCESO (EJ: PLANEACION, INSCRIPCION)
        'departamento', // DEPARTAMENTO QUE PERTENECE A ESE PROCESO
    ];

    /**
     * CONSTRUYE UN MAPA COMPLETO DE PROCESOS CON SUS DEPARTAMENTOS
     * TOMA COMO BASE EL MAPA DEL MODELO FORMATO (PROCESOS Y DEPARTAMENTOS PREDEFINIDOS)
     * LUGO AGREGA REGISTROS ADICIONALES QUE ESTÉN EN ESTA TABLA PERO NO EN EL MAPA BASE
     * AL FINAL, ORDENA ALFABÉTICAMENTE POR NOMBRE DE PROCESO
     */
    public static function mapa(): array
    {
        // TOMAR EL MAPA BASE DEL MODELO FORMATO (SI EXISTE)
        $base = [];
        if (class_exists(\App\Models\Formato::class) && method_exists(\App\Models\Formato::class, 'procesosYDepartamentos')) {
            $base = \App\Models\Formato::procesosYDepartamentos();
        }

        // OBTENER TODOS LOS REGISTROS DE ESTA TABLA ORDENADOS
        $rows = static::orderBy('proceso')->orderBy('departamento')->get();

        // AGREGAR CADA REGISTRO AL MAPA (EVITANDO DUPLICADOS)
        foreach ($rows as $row) {
            $proceso      = $row->proceso;
            $departamento = $row->departamento;

            if (!isset($base[$proceso])) {
                $base[$proceso] = [];
            }

            if (!in_array($departamento, $base[$proceso])) {
                $base[$proceso][] = $departamento;
            }
        }

        // ORDENAR EL MAPA FINAL POR NOMBRE DE PROCESO
        ksort($base);
        return $base;
    }
}
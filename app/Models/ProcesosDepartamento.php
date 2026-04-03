<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcesosDepartamento extends Model
{
    protected $table = 'procesos_departamentos';

    protected $fillable = [
        'proceso',
        'departamento',
    ];

    public static function mapa(): array
    {
        $base = [];
        if (class_exists(\App\Models\Formato::class) && method_exists(\App\Models\Formato::class, 'procesosYDepartamentos')) {
            $base = \App\Models\Formato::procesosYDepartamentos();
        }

        $rows = static::orderBy('proceso')->orderBy('departamento')->get();

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

        ksort($base);
        return $base;
    }
}
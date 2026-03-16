<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\RegistraHistorialVersiones;

class ProcesosDepartamento extends Model
{
    protected $table    = 'procesos_departamentos';
    protected $fillable = ['proceso', 'departamento'];

    /**
     * Devuelve el mapa proceso => [departamentos]
     * combinando los datos del modelo estático y los de la BD.
     */
    public static function mapa(): array
    {
        // Base estática del modelo Formato
        $base = Formato::procesosYDepartamentos();

        // Datos dinámicos de la BD
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
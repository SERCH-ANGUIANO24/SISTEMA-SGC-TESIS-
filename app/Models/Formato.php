<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Formato extends Model
{
    use HasFactory;

    protected $fillable = [
        'proceso',
        'departamento',
        'clave_formato',
        'codigo_procedimiento',
        'version_procedimiento',
        'nombre_archivo',
        'ruta_archivo',
        'extension_archivo',
        'tamanio_archivo',
    ];

    /**
     * Mapa de procesos y sus departamentos correspondientes.
     */
    public static function procesosYDepartamentos(): array
    {
        return [
            'PLANEACION' => [
                'RECTORIA',
                'DIRECCIÓN ACADÉMICA',
                'DIRECCIÓN DE ADMINISTRACIÓN',
                'FINANZAS',
            ],
            'PREINSCRIPCION' => [
                'SERVICIOS ESCOLARES',
            ],
            'REINSCRIPCION' => [
                'SERVICIOS ESCOLARES',
            ],
            'INSCRIPCION' => [
                'SERVICIOS ESCOLARES',
            ],
            'TITULACION' => [
                'SERVICIOS ESCOLARES',
            ],
            'ENSEÑANZA APRENDIZAJE' => [
                'DIRECCIÓN ACADÉMICA',
            ],
            'CONTRATACION U CONTROL DE PERSONAL' => [
                'RECURSOS HUMANOS',
            ],
            'VINCULACION' => [
                'VINCULACIÓN',
            ],
            'TECNOLOGIAS DE LA INFORMACION' => [
                'SISTEMAS COMPUTACIONALES',
            ],
            'GESTION DE RECURSOS' => [
                'RECURSOS FINANCIEROS',
                'ALMACÉN',
            ],
            'LABORATORIOS Y TALLERES' => [
                'ENCARGADO/A DE LABORATORIOS',
            ],
            'CENTRO DE INFORMACION' => [
                'BIBLIOTECA',
            ],
        ];
    }

    /**
     * Verifica si la clave de formato ya existe (excluyendo un ID específico).
     */
    public static function claveExiste(string $clave, ?int $excludeId = null): bool
    {
        $query = static::where('clave_formato', $clave);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->exists();
    }
}
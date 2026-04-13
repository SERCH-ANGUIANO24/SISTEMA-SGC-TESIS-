<?php
// app/Models/Formato.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\RegistraHistorialVersiones;

/**
 * MODELO PARA LA LISTA MAESTRA DE FORMATOS
 * ALMACENA DOCUMENTOS ORGANIZADOS POR PROCESO, DEPARTAMENTO Y CLAVES
 * REGISTRA AUTOMÁTICAMENTE LAS ACCIONES EN EL HISTORIAL
 */
class Formato extends Model
{
    use HasFactory, SoftDeletes, RegistraHistorialVersiones;  // SOFTDELETES: ELIMINACIÓN 

    // NOMBRE DE LA TABLA EN BASE DE DATOS
    protected $table = 'formatos';

    // CAMPOS QUE SE PUEDEN LLENAR DE FORMA MASIVA
    protected $fillable = [
        'proceso',               // NOMBRE DEL PROCESO (EJ: PLANEACION, INSCRIPCION)
        'departamento',          // DEPARTAMENTO RESPONSABLE
        'clave_formato',         // CLAVE ÚNICA DEL FORMATO
        'codigo_procedimiento',  // CÓDIGO DEL PROCEDIMIENTO ASOCIADO
        'version_procedimiento', // VERSIÓN DEL PROCEDIMIENTO
        'nombre_archivo',        // NOMBRE DEL ARCHIVO GUARDADO
        'ruta_archivo',          // RUTA DONDE ESTÁ EL ARCHIVO
        'extension_archivo',     // EXTENSIÓN (PDF, DOC, XLS, ETC)
        'tamanio_archivo',       // TAMAÑO EN BYTES
        'tipo_documento',        // CLASIFICACIÓN DEL DOCUMENTO
    ];

    // CONVIERTE AUTOMÁTICAMENTE ESTOS CAMPOS AL TIPO CORRECTO
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * MAPA DE PROCESOS Y SUS DEPARTAMENTOS CORRESPONDIENTES
     * SIRVE PARA VALIDAR Y MOSTRAR LAS OPCIONES EN FORMULARIOS
     * CADA PROCESO TIENE UNO O MÁS DEPARTAMENTOS ASIGNADOS
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
     * VERIFICA SI UNA CLAVE DE FORMATO YA EXISTE EN LA BASE DE DATOS
     * SE USA PARA EVITAR DUPLICADOS AL CREAR O EDITAR
     * EXCLUDEID SIRVE PARA IGNORAR EL PROPIO REGISTRO CUANDO SE EDITA
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
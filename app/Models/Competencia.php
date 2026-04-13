<?php
// app/Models/Competencia.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * MODELO PARA GESTIONAR COMPETENCIAS (CARPETAS Y DOCUMENTOS)
 * USA ESTRUCTURA JERÁRQUICA: LAS CARPETAS PUEDEN TENER SUB-CARPETAS O DOCUMENTOS
 */
class Competencia extends Model
{
    use HasFactory, SoftDeletes;  // SOFTDELETES: ELIMINACIÓN 

    // NOMBRE DE LA TABLA EN BASE DE DATOS
    protected $table = 'competencias';

    // CAMPOS QUE SE PUEDEN LLENAR DE FORMA MASIVA
    protected $fillable = [
        'nombre',               // NOMBRE DE LA CARPETA O DOCUMENTO
        'tipo',                 // 'carpeta' O 'documento'
        'archivo_nombre',       // NOMBRE DEL ARCHIVO GUARDADO
        'archivo_ruta',         // RUTA DONDE ESTÁ EL ARCHIVO
        'archivo_original',     // NOMBRE ORIGINAL DEL ARCHIVO SUBIDO
        'archivo_tamano',       // TAMAÑO EN BYTES
        'archivo_extension',    // EXTENSIÓN DEL ARCHIVO (PDF, DOC, ETC)
        'responsable',          // PERSONA RESPONSABLE
        'fecha_emision',        // FECHA DE EMISIÓN
        'fecha_vencimiento',    // FECHA DE VENCIMIENTO
        'descripcion',          // DESCRIPCIÓN DETALLADA
        'estado',               // ESTADO ACTUAL (ACTIVO, VENCIDO, ETC)
        'parent_id',            // ID DEL PADRE (PARA LA JERARQUÍA)
        'color'                 // COLOR PARA IDENTIFICAR EN LA VISTA
    ];

    // CONVIERTE AUTOMÁTICAMENTE ESTOS CAMPOS AL TIPO CORRECTO
    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'archivo_tamano' => 'integer',
        'deleted_at' => 'datetime',
    ];

    // OBTIENE LOS HIJOS (SUB-CARPETAS Y SUB-DOCUMENTOS) DE ESTA COMPETENCIA
    public function children()
    {
        return $this->hasMany(Competencia::class, 'parent_id');
    }

    // OBTIENE EL PADRE (LA CARPETA QUE CONTIENE A ESTA COMPETENCIA)
    public function parent()
    {
        return $this->belongsTo(Competencia::class, 'parent_id');
    }

    // OBTIENE SOLO LOS HIJOS QUE SON DOCUMENTOS (EXCLUYE CARPETAS)
    public function documentosHijos()
    {
        return $this->hasMany(Competencia::class, 'parent_id')->where('tipo', 'documento');
    }

    // FILTRO PARA OBTENER SOLO LAS CARPETAS
    public function scopeFolders($query)
    {
        return $query->where('tipo', 'carpeta');
    }

    // FILTRO PARA OBTENER SOLO LOS DOCUMENTOS
    public function scopeDocuments($query)
    {
        return $query->where('tipo', 'documento');
    }

    // PREGUNTA: ¿ESTO ES UNA CARPETA?
    public function isFolder()
    {
        return $this->tipo === 'carpeta';
    }

    // PREGUNTA: ¿ESTO ES UN DOCUMENTO?
    public function isDocument()
    {
        return $this->tipo === 'documento';
    }

    // CALCULA EL TOTAL DE ELEMENTOS DENTRO (CARPETAS + DOCUMENTOS)
    public function getTotalItemsCountAttribute()
    {
        return $this->children()->count() + $this->documentosHijos()->count();
    }

    // DEVUELVE EL NOMBRE ORIGINAL DEL ARCHIVO (O UNO GENERADO SI NO EXISTE)
    public function getOriginalNameAttribute()
    {
        return $this->archivo_original ?: $this->nombre . '.' . $this->archivo_extension;
    }

    // CONVIERTE EL TAMAÑO DE BYTES A FORMATO LEGIBLE (KB, MB, GB)
    public function getFormattedSizeAttribute()
    {
        if (!$this->archivo_tamano) return '0 B';
        $bytes = $this->archivo_tamano;
        $units = ['B', 'KB', 'MB', 'GB'];
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
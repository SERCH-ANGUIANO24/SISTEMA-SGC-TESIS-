<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\RegistraHistorialVersiones;

/**
 * MODELO PARA DOCUMENTOS DE GESTIÓN DOCUMENTAL
 * REPRESENTA ARCHIVOS ORGANIZADOS EN CARPETAS, CON METADATOS COMO PROCESO, DEPARTAMENTO, VERSIONES, ETC
 * REGISTRA AUTOMÁTICAMENTE LAS ACCIONES EN EL HISTORIAL
 */
class DocumentalDocument extends Model
{
    use HasFactory, SoftDeletes, RegistraHistorialVersiones;  // SOFTDELETES: ELIMINACIÓN 

    // NOMBRE DE LA TABLA EN BASE DE DATOS
    protected $table = 'documental_documents';

    // CAMPOS QUE SE PUEDEN LLENAR DE FORMA MASIVA
    protected $fillable = [
        'name',                 // NOMBRE DEL ARCHIVO GUARDADO
        'original_name',        // NOMBRE ORIGINAL DEL ARCHIVO SUBIDO
        'file_path',            // RUTA DONDE ESTÁ GUARDADO
        'mime_type',            // TIPO MIME (application/pdf, image/jpg, ETC)
        'size',                 // TAMAÑO EN BYTES
        'extension',            // EXTENSIÓN DEL ARCHIVO (pdf, doc, jpg)
        'folder_id',            // ID DE LA CARPETA DONDE PERTENECE
        'user_id',              // ID DEL USUARIO QUE SUBIÓ EL ARCHIVO
        'responsable',          // PERSONA RESPONSABLE DEL DOCUMENTO
        'proceso',              // PROCESO AL QUE PERTENECE
        'departamento',         // DEPARTAMENTO RELACIONADO
        'clave_formato',        // CLAVE O CÓDIGO DEL FORMATO
        'codigo_procedimiento', // CÓDIGO DEL PROCEDIMIENTO ASOCIADO
        'version_procedimiento',// VERSIÓN DEL PROCEDIMIENTO
        'estatus',              // ESTADO ACTUAL DEL DOCUMENTO
        'observaciones',        // COMENTARIOS O NOTAS ADICIONALES
        'fecha',                // FECHA DEL DOCUMENTO
        'tipo_documento',       // CLASIFICACIÓN DEL TIPO DE DOCUMENTO
    ];

    // CONVIERTE AUTOMÁTICAMENTE ESTOS CAMPOS AL TIPO CORRECTO
    protected $casts = [
        'fecha' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // RELACIÓN: PERTENECE A UNA CARPETA DE GESTIÓN DOCUMENTAL
    public function folder()
    {
        return $this->belongsTo(DocumentalFolder::class, 'folder_id');
    }

    // RELACIÓN: PERTENECE A UN USUARIO (QUIEN LO SUBIÓ)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // DEVUELVE EL NOMBRE COMPLETO: nombre.extensión
    public function getFullNameAttribute()
    {
        return $this->name . '.' . $this->extension;
    }

    // CONVIERTE EL TAMAÑO DE BYTES A FORMATO LEGIBLE (B, KB, MB, GB)
    public function getFormattedSizeAttribute()
    {
        $bytes = $this->size;
        if ($bytes < 1024) return $bytes . ' B';
        elseif ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        elseif ($bytes < 1073741824) return round($bytes / 1048576, 1) . ' MB';
        else return round($bytes / 1073741824, 1) . ' GB';
    }

    // DEVUELVE EL ÍCONO SEGÚN LA EXTENSIÓN DEL ARCHIVO (PDF, WORD, EXCEL, IMAGEN, ETC)
    public function getIconAttribute()
    {
        $icons = [
            'pdf'  => 'bi-file-pdf',
            'doc'  => 'bi-file-word',
            'docx' => 'bi-file-word',
            'xls'  => 'bi-file-excel',
            'xlsx' => 'bi-file-excel',
            'jpg'  => 'bi-file-image',
            'jpeg' => 'bi-file-image',
            'png'  => 'bi-file-image',
            'gif'  => 'bi-file-image',
            'txt'  => 'bi-file-text',
        ];
        return $icons[strtolower($this->extension)] ?? 'bi-file-earmark';
    }

    // PREGUNTA: ¿SE PUEDE VER EN EL NAVEGADOR? (PDF, IMÁGENES Y TXT SÍ SE PUEDEN)
    public function getCanPreviewAttribute()
    {
        $previewable = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'txt'];
        return in_array(strtolower($this->extension), $previewable);
    }
}
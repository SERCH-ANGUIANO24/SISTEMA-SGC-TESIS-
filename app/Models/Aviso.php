<?php
// app/Models/Aviso.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use App\Traits\RegistraHistorialVersiones;

/**
 * MODELO PARA LOS AVISOS O COMUNICADOS DEL SISTEMA
 * PUEDEN TENER ARCHIVOS ADJUNTOS Y FECHAS DE VIGENCIA
 * SE REGISTRA AUTOMÁTICAMENTE EN EL HISTORIAL
 */
class Aviso extends Model
{
    // HASFACTORY: DATOS DE PRUEBA | SOFTDELETES: ELIMINACIÓN  | REGISTRAHISTORIAL: GUARDA ACCIONES
    use HasFactory, SoftDeletes, RegistraHistorialVersiones;

    // NOMBRE DE LA TABLA EN LA BASE DE DATOS
    protected $table = 'avisos';

    // CAMPOS QUE SE PUEDEN LLENAR DE FORMA MASIVA
    protected $fillable = [
        'titulo',           // TÍTULO DEL AVISO
        'descripcion',      // DESCRIPCIÓN COMPLETA
        'archivo_path',     // RUTA DEL ARCHIVO EN EL SERVIDOR
        'archivo_nombre',   // NOMBRE ORIGINAL DEL ARCHIVO
        'tipo_archivo',     // EXTENSIÓN DEL ARCHIVO
        'tamano_archivo',   // TAMAÑO EN BYTES
        'fecha_inicio',     // FECHA QUE EMPIEZA A MOSTRARSE
        'fecha_fin',        // FECHA QUE DEJA DE MOSTRARSE
        'activo',           // ENCENDIDO O APAGADO
        'visitas',          // CONTADOR DE VISTAS
        'created_by'        // ID DEL USUARIO QUE LO CREÓ
    ];

    // CONVIERTE AUTOMÁTICAMENTE ESTOS CAMPOS AL TIPO CORRECTO
    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'activo' => 'boolean',
        'visitas' => 'integer',
        'deleted_at' => 'datetime',
    ];

    // RELACIÓN: QUIÉN CREÓ ESTE AVISO (PERTENECE A UN USUARIO)
    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // VERIFICA SI EL AVISO ESTÁ VIGENTE AHORA MISMO
    public function isActive()
    {
        $now = now();
        return $this->activo && 
               $now >= $this->fecha_inicio && 
               $now <= $this->fecha_fin;
    }

    // DEVUELVE EL ÍCONO SEGÚN EL TIPO DE ARCHIVO (PDF, WORD, IMAGEN, ETC)
    public function getIconoArchivo()
    {
        $extensiones = [
            'pdf' => 'bi-file-pdf',
            'doc' => 'bi-file-word',
            'docx' => 'bi-file-word',
            'xls' => 'bi-file-excel',
            'xlsx' => 'bi-file-excel',
            'ppt' => 'bi-file-ppt',
            'pptx' => 'bi-file-ppt',
            'jpg' => 'bi-file-image',
            'jpeg' => 'bi-file-image',
            'png' => 'bi-file-image',
            'gif' => 'bi-file-image',
            'txt' => 'bi-file-text',
            'zip' => 'bi-file-zip',
            'rar' => 'bi-file-zip',
        ];

        $extension = strtolower(pathinfo($this->archivo_nombre, PATHINFO_EXTENSION));
        return $extensiones[$extension] ?? 'bi-file-earmark';
    }

    // DEVUELVE LA URL PARA VER O DESCARGAR EL ARCHIVO
    public function getArchivoUrl()
    {
        return $this->archivo_path ? Storage::url($this->archivo_path) : null;
    }

    // FILTRO PARA OBTENER SOLO LOS AVISOS VIGENTES (SCOPE REUTILIZABLE)
    public function scopeActivos($query)
    {
        $now = now();
        return $query->where('activo', true)
                     ->where('fecha_inicio', '<=', $now)
                     ->where('fecha_fin', '>=', $now);
    }
}
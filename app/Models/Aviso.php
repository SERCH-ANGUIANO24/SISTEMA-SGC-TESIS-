<?php
// app/Models/Aviso.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use App\Traits\RegistraHistorialVersiones;

class Aviso extends Model
{
    use HasFactory, SoftDeletes, RegistraHistorialVersiones;

    protected $table = 'avisos';

    protected $fillable = [
        'titulo',
        'descripcion',
        'archivo_path',
        'archivo_nombre',
        'tipo_archivo',
        'tamano_archivo',
        'fecha_inicio',
        'fecha_fin',
        'activo',
        'visitas',
        'created_by'
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'activo' => 'boolean',
        'visitas' => 'integer',
        'deleted_at' => 'datetime',
    ];

    // Relación con el usuario que creó el aviso
    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Verificar si el aviso está actualmente activo
    public function isActive()
    {
        $now = now();
        return $this->activo && 
               $now >= $this->fecha_inicio && 
               $now <= $this->fecha_fin;
    }

    // Obtener el icono según el tipo de archivo
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

    // Obtener URL del archivo
    public function getArchivoUrl()
    {
        return $this->archivo_path ? Storage::url($this->archivo_path) : null;
    }

    // Scope para avisos activos
    public function scopeActivos($query)
    {
        $now = now();
        return $query->where('activo', true)
                     ->where('fecha_inicio', '<=', $now)
                     ->where('fecha_fin', '>=', $now);
    }
}
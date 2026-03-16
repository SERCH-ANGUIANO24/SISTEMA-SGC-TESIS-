<?php
// app/Models/Competencia.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RegistraHistorialVersiones;

class Competencia extends Model
{
    use HasFactory, RegistraHistorialVersiones;

    protected $table = 'competencias';

    protected $fillable = [
        'nombre',
        'tipo',
        'archivo_nombre',
        'archivo_ruta',
        'archivo_original',
        'archivo_tamano',
        'archivo_extension',
        'responsable',
        'fecha_emision',
        'fecha_vencimiento',
        'descripcion',
        'estado',
        'parent_id',
        'color'
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'archivo_tamano' => 'integer'
    ];

    // Relación para estructura de carpetas (hijos)
    public function children()
    {
        return $this->hasMany(Competencia::class, 'parent_id');
    }

    // Relación para estructura de carpetas (padre)
    public function parent()
    {
        return $this->belongsTo(Competencia::class, 'parent_id');
    }

    // Relación para contar documentos dentro de la carpeta
    public function documentosHijos()
    {
        return $this->hasMany(Competencia::class, 'parent_id')->where('tipo', 'documento');
    }

    // SCOPE para obtener solo carpetas
    public function scopeFolders($query)
    {
        return $query->where('tipo', 'carpeta');
    }

    // SCOPE para obtener solo documentos
    public function scopeDocuments($query)
    {
        return $query->where('tipo', 'documento');
    }

    // Verificar si es carpeta
    public function isFolder()
    {
        return $this->tipo === 'carpeta';
    }

    // Verificar si es documento
    public function isDocument()
    {
        return $this->tipo === 'documento';
    }

    // Método para contar todos los elementos dentro de la carpeta
    public function getTotalItemsCountAttribute()
    {
        return $this->children()->count() + $this->documentosHijos()->count();
    }

    // Obtener el nombre original del archivo
    public function getOriginalNameAttribute()
    {
        return $this->archivo_original ?: $this->nombre . '.' . $this->archivo_extension;
    }

    // Obtener tamaño formateado
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
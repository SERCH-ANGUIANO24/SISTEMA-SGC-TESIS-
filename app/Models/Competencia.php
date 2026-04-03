<?php
// app/Models/Competencia.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Competencia extends Model
{
    use HasFactory, SoftDeletes;

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
        'archivo_tamano' => 'integer',
        'deleted_at' => 'datetime',
    ];

    public function children()
    {
        return $this->hasMany(Competencia::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(Competencia::class, 'parent_id');
    }

    public function documentosHijos()
    {
        return $this->hasMany(Competencia::class, 'parent_id')->where('tipo', 'documento');
    }

    public function scopeFolders($query)
    {
        return $query->where('tipo', 'carpeta');
    }

    public function scopeDocuments($query)
    {
        return $query->where('tipo', 'documento');
    }

    public function isFolder()
    {
        return $this->tipo === 'carpeta';
    }

    public function isDocument()
    {
        return $this->tipo === 'documento';
    }

    public function getTotalItemsCountAttribute()
    {
        return $this->children()->count() + $this->documentosHijos()->count();
    }

    public function getOriginalNameAttribute()
    {
        return $this->archivo_original ?: $this->nombre . '.' . $this->archivo_extension;
    }

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
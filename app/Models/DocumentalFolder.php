<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\RegistraHistorialVersiones;

class DocumentalFolder extends Model
{
    use HasFactory, SoftDeletes, RegistraHistorialVersiones;

    protected $table = 'documental_folders';

    protected $fillable = ['name', 'color', 'parent_id', 'user_id'];

    protected $dates = ['deleted_at'];

    /**
     * Relación con el padre - con protección contra nulos
     */
    public function parent()
    {
        return $this->belongsTo(DocumentalFolder::class, 'parent_id')->withDefault();
    }

    public function subfolders()
    {
        return $this->hasMany(DocumentalFolder::class, 'parent_id');
    }

    public function documents()
    {
        return $this->hasMany(DocumentalDocument::class, 'folder_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener ruta completa de la carpeta (protegido contra ciclos)
     */
    public function getFullPathAttribute()
    {
        $path = [];
        $current = $this;
        $visitedIds = [];
        $maxDepth = 50;
        $depth = 0;
        
        while ($current && $depth < $maxDepth) {
            if (in_array($current->id, $visitedIds)) {
                break; // Ciclo detectado
            }
            $visitedIds[] = $current->id;
            $path[] = $current->name;
            $current = $current->parent;
            $depth++;
        }
        
        return implode(' / ', array_reverse($path));
    }

    /**
     * Obtener todas las subcarpetas (incluyendo eliminadas para restauración)
     */
    public function subfoldersWithTrashed()
    {
        return $this->hasMany(DocumentalFolder::class, 'parent_id')->withTrashed();
    }

    /**
     * Obtener todos los documentos (incluyendo eliminados para restauración)
     */
    public function documentsWithTrashed()
    {
        return $this->hasMany(DocumentalDocument::class, 'folder_id')->withTrashed();
    }
}
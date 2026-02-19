<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormatoFolder extends Model
{
    use HasFactory;

    protected $table = 'formatos_folders';

    protected $fillable = ['name', 'color', 'parent_id', 'user_id'];

    // Relación: carpeta padre
    public function parent()
    {
        return $this->belongsTo(FormatoFolder::class, 'parent_id');
    }

    // Subcarpetas
    public function subfolders()
    {
        return $this->hasMany(FormatoFolder::class, 'parent_id');
    }

    // Documentos dentro de esta carpeta
    public function documents()
    {
        return $this->hasMany(FormatoDocument::class, 'folder_id');
    }

    // Usuario dueño
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Obtener ruta completa
    public function getFullPathAttribute()
    {
        if ($this->parent) {
            return $this->parent->full_path . '/' . $this->name;
        }
        return $this->name;
    }
}
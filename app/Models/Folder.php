<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'color', 'parent_id', 'user_id'];

    // Relación: carpeta padre
    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    // Subcarpetas
    public function subfolders()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    // Documentos dentro de esta carpeta
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    // Dueño de la carpeta
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * MODELO PARA CARPETAS DE ANEXOS
 * ORGANIZA DOCUMENTOS Y SUB-CARPETAS DE FORMA JERÁRQUICA (ESTRUCTURA DE ÁRBOL)
 */
class Folder extends Model
{
    use HasFactory, SoftDeletes;  // SOFTDELETES: ELIMINACIÓN SUAVE

    // CAMPOS QUE SE PUEDEN LLENAR DE FORMA MASIVA
    protected $fillable = [
        'name',         // NOMBRE DE LA CARPETA
        'color',        // COLOR PARA IDENTIFICARLA VISUALMENTE
        'parent_id',    // ID DE LA CARPETA PADRE (NULL SI ES RAÍZ)
        'user_id'       // ID DEL USUARIO QUE CREÓ LA CARPETA
    ];

    // CAMPOS QUE SE TRATAN COMO FECHAS
    protected $dates = ['deleted_at'];

    // RELACIÓN: OBTIENE LA CARPETA PADRE (DONDE ESTÁ DENTRO)
    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    // RELACIÓN: OBTIENE LAS SUB-CARPETAS (LAS QUE ESTÁN DENTRO)
    public function subfolders()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    // RELACIÓN: OBTIENE LOS DOCUMENTOS DENTRO DE ESTA CARPETA
    public function documents()
    {
        return $this->hasMany(Document::class, 'folder_id');
    }

    // RELACIÓN: OBTIENE EL USUARIO QUE CREÓ LA CARPETA
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
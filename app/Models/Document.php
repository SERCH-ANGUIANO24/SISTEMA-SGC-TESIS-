<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * MODELO PARA DOCUMENTOS (ANEXOS)
 * REPRESENTA ARCHIVOS SUBIDOS AL SISTEMA, ORGANIZADOS DENTRO DE CARPETAS
 */
class Document extends Model
{
    use HasFactory, SoftDeletes;  // SOFTDELETES: ELIMINACIÓN  (NO BORRA FÍSICAMENTE)

    // CAMPOS QUE SE PUEDEN LLENAR DE FORMA MASIVA
    protected $fillable = [
        'name',             // NOMBRE DEL ARCHIVO GUARDADO EN EL SERVIDOR
        'original_name',    // NOMBRE ORIGINAL QUE TENÍA EL ARCHIVO AL SUBIRLO
        'file_path',        // RUTA DONDE ESTÁ GUARDADO EL ARCHIVO
        'mime_type',        // TIPO DE ARCHIVO (PDF, JPG, DOC, ETC)
        'size',             // TAMAÑO DEL ARCHIVO EN BYTES
        'folder_id',        // ID DE LA CARPETA DONDE PERTENECE
        'user_id'           // ID DEL USUARIO QUE SUBIÓ EL ARCHIVO
    ];

    // CAMPOS QUE SE TRATAN COMO FECHAS
    protected $dates = ['deleted_at'];

    // RELACIÓN: UN DOCUMENTO PERTENECE A UNA CARPETA
    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    // RELACIÓN: UN DOCUMENTO FUE SUBIDO POR UN USUARIO
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
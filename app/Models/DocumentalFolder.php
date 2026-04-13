<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\RegistraHistorialVersiones;

/**
 * MODELO PARA CARPETAS DE GESTIÓN DOCUMENTAL
 * ORGANIZA DOCUMENTOS Y SUB-CARPETAS DE FORMA JERÁRQUICA
 * REGISTRA AUTOMÁTICAMENTE LAS ACCIONES EN EL HISTORIAL
 */
class DocumentalFolder extends Model
{
    use HasFactory, SoftDeletes, RegistraHistorialVersiones;  // SOFTDELETES: ELIMINACIÓN 

    // NOMBRE DE LA TABLA EN BASE DE DATOS
    protected $table = 'documental_folders';

    // CAMPOS QUE SE PUEDEN LLENAR DE FORMA MASIVA
    protected $fillable = ['name', 'color', 'parent_id', 'user_id'];

    // CAMPOS QUE SE TRATAN COMO FECHAS
    protected $dates = ['deleted_at'];

    /**
     * RELACIÓN CON LA CARPETA PADRE
     * withDefault() EVITA ERRORES SI NO HAY PADRE (CARPETA RAÍZ)
     */
    public function parent()
    {
        return $this->belongsTo(DocumentalFolder::class, 'parent_id')->withDefault();
    }

    // RELACIÓN: OBTIENE LAS SUB-CARPETAS DENTRO DE ESTA CARPETA
    public function subfolders()
    {
        return $this->hasMany(DocumentalFolder::class, 'parent_id');
    }

    // RELACIÓN: OBTIENE LOS DOCUMENTOS DENTRO DE ESTA CARPETA
    public function documents()
    {
        return $this->hasMany(DocumentalDocument::class, 'folder_id');
    }

    // RELACIÓN: OBTIENE EL USUARIO QUE CREÓ LA CARPETA
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * OBTIENE LA RUTA COMPLETA DE LA CARPETA (EJ: "PRINCIPAL / RECURSOS / 2024")
     * TIENE PROTECCIÓN CONTRA CICLOS INFINITOS (MÁXIMO 50 NIVELES)
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
                break; // DETECTA CICLOS PARA EVITAR BUCLE INFINITO
            }
            $visitedIds[] = $current->id;
            $path[] = $current->name;
            $current = $current->parent;
            $depth++;
        }
        
        return implode(' / ', array_reverse($path));
    }

    /**
     * OBTIENE TODAS LAS SUB-CARPETAS, INCLUSIVE LAS ELIMINADAS
     * ÚTIL PARA RESTAURAR CARPETAS COMPLETAS
     */
    public function subfoldersWithTrashed()
    {
        return $this->hasMany(DocumentalFolder::class, 'parent_id')->withTrashed();
    }

    /**
     * OBTIENE TODOS LOS DOCUMENTOS, INCLUSIVE LOS ELIMINADOS
     * ÚTIL PARA RESTAURAR DOCUMENTOS JUNTO CON SU CARPETA
     */
    public function documentsWithTrashed()
    {
        return $this->hasMany(DocumentalDocument::class, 'folder_id')->withTrashed();
    }
}
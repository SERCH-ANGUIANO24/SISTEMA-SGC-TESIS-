<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RegistraHistorialVersiones;

class MatrizDocument extends Model
{
    use HasFactory, RegistraHistorialVersiones;

    protected $table = 'matrices_documents';

    protected $fillable = [
        'name',
        'original_name',
        'file_path',
        'mime_type',
        'size',
        'extension',
        'folder_id',
        'user_id',
        'tipo_documento',
        'fecha_documento'
    ];

    protected $casts = [
        'fecha_documento' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relación con carpeta
    public function folder()
    {
        return $this->belongsTo(MatrizFolder::class, 'folder_id');
    }

    // Relación con usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Obtener nombre completo con extensión
    public function getFullNameAttribute()
    {
        return $this->name . '.' . $this->extension;
    }

    // Obtener tamaño formateado
    public function getFormattedSizeAttribute()
    {
        $bytes = $this->size;
        
        if ($bytes < 1024) {
            return $bytes . ' B';
        } elseif ($bytes < 1048576) {
            return round($bytes / 1024, 1) . ' KB';
        } elseif ($bytes < 1073741824) {
            return round($bytes / 1048576, 1) . ' MB';
        } else {
            return round($bytes / 1073741824, 1) . ' GB';
        }
    }

    // Obtener ícono según tipo de documento
    public function getIconAttribute()
    {
        $extension = strtolower($this->extension);
        
        $icons = [
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
        ];

        return $icons[$extension] ?? 'bi-file-earmark';
    }

    // Obtener tipo de documento legible
    public function getTipoDocumentoAttribute($value)
    {
        if ($value) {
            return $value;
        }
        
        // Si no hay tipo asignado, inferir de la extensión
        $extension = strtolower($this->extension);
        
        $tipos = [
            'pdf' => 'PDF',
            'xls' => 'Excel',
            'xlsx' => 'Excel',
            'doc' => 'Word',
            'docx' => 'Word',
            'ppt' => 'PowerPoint',
            'pptx' => 'PowerPoint',
            'jpg' => 'Imagen',
            'jpeg' => 'Imagen',
            'png' => 'Imagen',
            'gif' => 'Imagen',
            'txt' => 'Texto',
        ];
        
        return $tipos[$extension] ?? 'Documento';
    }

    // Verificar si se puede previsualizar
    public function getCanPreviewAttribute()
    {
        $previewable = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'txt'];
        return in_array(strtolower($this->extension), $previewable);
    }
}
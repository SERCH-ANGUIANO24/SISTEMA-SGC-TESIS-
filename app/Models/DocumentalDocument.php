<?php
// app/Models/DocumentalDocument.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentalDocument extends Model
{
    use HasFactory;

    protected $table = 'documental_documents';

    protected $fillable = [
        'name',
        'original_name',
        'file_path',
        'mime_type',
        'size',
        'extension',
        'folder_id',
        'user_id',
        'responsable',
        'proceso',
        'departamento',
        'estatus',
        'observaciones',
        'fecha'
    ];

    protected $casts = [
        'fecha' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relación con carpeta
    public function folder()
    {
        return $this->belongsTo(DocumentalFolder::class, 'folder_id');
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

    // Obtener ícono según extensión
    public function getIconAttribute()
    {
        $icons = [
            'pdf' => 'bi-file-pdf',
            'doc' => 'bi-file-word',
            'docx' => 'bi-file-word',
            'xls' => 'bi-file-excel',
            'xlsx' => 'bi-file-excel',
            'jpg' => 'bi-file-image',
            'jpeg' => 'bi-file-image',
            'png' => 'bi-file-image',
            'gif' => 'bi-file-image',
            'txt' => 'bi-file-text',
        ];

        return $icons[strtolower($this->extension)] ?? 'bi-file-earmark';
    }

    // Verificar si se puede previsualizar
    public function getCanPreviewAttribute()
    {
        $previewable = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'txt'];
        return in_array(strtolower($this->extension), $previewable);
    }
}
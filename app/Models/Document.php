<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RegistraHistorialVersiones;

class Document extends Model
{
    use HasFactory, RegistraHistorialVersiones;

    protected $fillable = [
        'name', 'original_name', 'file_path', 'mime_type', 'size', 'folder_id', 'user_id'
    ];

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
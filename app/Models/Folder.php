<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\RegistraHistorialVersiones;

class Folder extends Model
{
    use HasFactory, RegistraHistorialVersiones;

    protected $fillable = [
        'name', 'color', 'parent_id', 'user_id'
    ];

    public function parent()
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function subfolders()
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'folder_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
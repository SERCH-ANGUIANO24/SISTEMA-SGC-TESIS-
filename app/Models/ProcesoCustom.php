<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\RegistraHistorialVersiones;

class ProcesoCustom extends Model
{
    protected $table = 'procesos_custom';

    protected $fillable = [
        'proceso',
        'departamento',
    ];
}
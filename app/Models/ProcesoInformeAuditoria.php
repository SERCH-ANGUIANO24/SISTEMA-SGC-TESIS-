<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\RegistraHistorialVersiones;

class ProcesoInformeAuditoria extends Model
{
    protected $table = 'procesos_informe_auditoria';

    protected $fillable = ['nombre'];
}
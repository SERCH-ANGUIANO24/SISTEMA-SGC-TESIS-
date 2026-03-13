<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcesoInformeAuditoria extends Model
{
    protected $table = 'procesos_informe_auditoria';

    protected $fillable = ['nombre'];
}
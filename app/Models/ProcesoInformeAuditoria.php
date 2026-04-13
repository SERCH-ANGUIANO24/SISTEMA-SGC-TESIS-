<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\RegistraHistorialVersiones;

/**
 * MODELO PARA LOS PROCESOS QUE SE AUDITAN EN UN INFORME
 * LISTA DE PROCESOS DISPONIBLES PARA SELECCIONAR EN INFORMES DE AUDITORÍA
 */
class ProcesoInformeAuditoria extends Model
{
    // NOMBRE DE LA TABLA EN BASE DE DATOS
    protected $table = 'procesos_informe_auditoria';

    // CAMPOS QUE SE PUEDEN LLENAR DE FORMA MASIVA
    protected $fillable = ['nombre'];  // NOMBRE DEL PROCESO (EJ: PLANEACION, INSCRIPCION)
}
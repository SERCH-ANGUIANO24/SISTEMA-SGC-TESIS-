<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * MODELO QUE REPRESENTA LAS AUDITORÍAS DEL SISTEMA (PLAN DE AUDITORÍA)
 * ALMACENA INFORMACIÓN DE CADA AUDITORÍA: NOMBRE, TIPO, AUDITORES, FECHAS Y ARCHIVO ADJUNTO
 * USA SOFT DELETES PARA NO PERDER DATOS CUANDO SE ELIMINA UNA AUDITORÍA
 */
class Auditoria extends Model
{
    // HASFACTORY: PERMITE USAR FACTORÍAS PARA GENERAR DATOS DE PRUEBA
    // SOFTDELETES: AGREGA EL CAMPO 'deleted_at' PARA ELIMINACIÓN (NO BORRA FÍSICAMENTE)
    use HasFactory, SoftDeletes;

    // NOMBRE DE LA TABLA EN LA BASE DE DATOS
    protected $table = 'auditorias';

    /**
     * CAMPOS QUE SE PUEDEN ASIGNAR DE FORMA MASIVA (MEDIANTE create() O update())
     * ESTOS SON LOS ÚNICOS CAMPOS QUE EL USUARIO PUEDE LLENAR DIRECTAMENTE
     */
    protected $fillable = [
        'nombre_auditoria',   // NOMBRE O TÍTULO DE LA AUDITORÍA
        'tipo_auditoria',     // TIPO: INTERNA O EXTERNA
        'auditor_lider',      // NOMBRE DEL AUDITOR PRINCIPAL
        'fecha_auditoria',    // CAMPO EXISTENTE EN LA TABLA (COMPATIBILIDAD)
        'fecha_inicio',       // FECHA DE INICIO DE LA AUDITORÍA
        'fecha_fin',          // FECHA DE FINALIZACIÓN DE LA AUDITORÍA
        'anio',               // AÑO AL QUE PERTENECE LA AUDITORÍA (PARA FILTROS)
        'auditores',          // LISTA DE AUDITORES PARTICIPANTES (TEXTO LIBRE)
        'archivo_path',       // RUTA DEL ARCHIVO ADJUNTO EN EL SERVIDOR
        'archivo_nombre'      // NOMBRE ORIGINAL DEL ARCHIVO SUBIDO
    ];

    /**
     * CASTEO DE CAMPOS - CONVIERTE AUTOMÁTICAMENTE LOS TIPOS DE DATOS
     * AL OBTENER O GUARDAR INFORMACIÓN EN LA BASE DE DATOS
     */
    protected $casts = [
        'fecha_auditoria' => 'date',   // CONVIERTE A OBJETO CARBON (FECHA)
        'fecha_inicio' => 'date',      // CONVIERTE A OBJETO CARBON (FECHA)
        'fecha_fin' => 'date',         // CONVIERTE A OBJETO CARBON (FECHA)
        'anio' => 'integer',           // CONVIERTE A ENTERO
        'created_at' => 'datetime',    // FECHA DE CREACIÓN DEL REGISTRO
        'updated_at' => 'datetime',    // FECHA DE ÚLTIMA ACTUALIZACIÓN
        'deleted_at' => 'datetime',    // FECHA DE ELIMINACIÓN (SOFT DELETE)
    ];
}
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('solicitudes_mejora', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_solicitud');
            $table->date('fecha_solicitud');
            $table->string('folio_solicitud')->nullable();
            $table->string('responsable_accion');
            $table->date('fecha_aplicacion');
            $table->text('actividades_verificacion')->nullable();
            $table->date('fecha_verificacion')->nullable();
            $table->enum('estatus', ['Abierto', 'En Proceso', 'Cerrado'])->default('Abierto');
            
            // ✅ ESTAS DOS COLUMNAS SON LAS QUE FALTABAN
            $table->string('archivo_nombre')->nullable();
            $table->string('archivo_ruta')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('solicitudes_mejora');
    }
};
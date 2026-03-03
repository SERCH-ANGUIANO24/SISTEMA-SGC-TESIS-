<?php
// database/migrations/2024_01_01_000001_create_competencias_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('competencias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('tipo'); // 'carpeta', 'documento'
            $table->string('archivo_nombre')->nullable();
            $table->string('archivo_ruta')->nullable();
            $table->string('archivo_original')->nullable();
            $table->integer('archivo_tamano')->nullable();
            $table->string('archivo_extension')->nullable();
            $table->string('responsable')->nullable();
            $table->date('fecha_emision')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('estado')->default('activo');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('color')->default('#800000');
            $table->timestamps();
            
            $table->foreign('parent_id')->references('id')->on('competencias')->onDelete('cascade');
            $table->index(['tipo', 'parent_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('competencias');
    }
};
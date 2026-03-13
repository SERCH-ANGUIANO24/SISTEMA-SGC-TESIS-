<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formatos', function (Blueprint $table) {
            $table->id();
            $table->string('proceso');
            $table->string('departamento');
            $table->string('clave_formato');
            $table->string('codigo_procedimiento');
            $table->string('version_procedimiento');
            $table->string('nombre_archivo');
            $table->string('ruta_archivo');
            $table->string('extension_archivo')->nullable();
            $table->unsignedBigInteger('tamanio_archivo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formatos');
    }
};
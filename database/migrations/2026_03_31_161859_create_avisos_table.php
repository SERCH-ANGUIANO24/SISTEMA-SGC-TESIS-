<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('avisos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin');
            $table->unsignedBigInteger('created_by');
            $table->boolean('activo')->default(1);
            $table->string('archivo_path')->nullable();
            $table->string('archivo_nombre')->nullable();
            $table->string('tipo_archivo')->nullable();
            $table->unsignedBigInteger('tamano_archivo')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('avisos');
    }
};
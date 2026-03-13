<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('historial_versiones', function (Blueprint $table) {
            $table->id();
            $table->string('usuario_nombre');
            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->string('usuario_email')->nullable();
            $table->string('usuario_rol')->nullable();
            $table->string('modulo');
            $table->string('accion');
            $table->text('descripcion');
            $table->string('nivel_importancia')->default('normal');
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('tabla_afectada')->nullable();
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->string('elemento_nombre')->nullable();
            $table->timestamps();

            $table->index(['usuario_id', 'created_at']);
            $table->index(['modulo', 'accion']);
            $table->index('created_at');
            $table->index('nivel_importancia');
            $table->index('tabla_afectada');
        });
    }

    public function down()
    {
        Schema::dropIfExists('historial_versiones');
    }
};
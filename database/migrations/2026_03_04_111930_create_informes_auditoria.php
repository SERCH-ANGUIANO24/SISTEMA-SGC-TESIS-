<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('informes_auditoria', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_informe');
            $table->enum('tipo_auditoria', ['Interna', 'Externa']);
            $table->string('auditor_lider');
            $table->date('fecha_informe');
            $table->date('fecha_auditoria');
            $table->unsignedBigInteger('auditoria_relacionada_id')->nullable();
            $table->json('procesos_auditados')->nullable();
            $table->unsignedInteger('no_conformidades')->default(0);
            $table->unsignedInteger('oportunidades_mejora')->default(0);
            $table->string('documento_path')->nullable();
            $table->string('documento_nombre')->nullable();
            $table->timestamps();

            $table->foreign('auditoria_relacionada_id')
                  ->references('id')
                  ->on('auditorias')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('informes_auditoria');
    }
};
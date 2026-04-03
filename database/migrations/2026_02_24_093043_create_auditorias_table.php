<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Verificar si la tabla existe y tiene la estructura correcta
        if (Schema::hasTable('auditorias')) {
            // Agregar columnas faltantes
            Schema::table('auditorias', function (Blueprint $table) {
                if (!Schema::hasColumn('auditorias', 'fecha_inicio')) {
                    $table->date('fecha_inicio')->nullable()->after('auditor_lider');
                }
                if (!Schema::hasColumn('auditorias', 'fecha_fin')) {
                    $table->date('fecha_fin')->nullable()->after('fecha_inicio');
                }
                if (!Schema::hasColumn('auditorias', 'deleted_at')) {
                    $table->softDeletes();
                }
                if (!Schema::hasColumn('auditorias', 'status')) {
                    $table->string('status')->default('activo')->after('archivo_nombre');
                }
            });
        } else {
            // Crear tabla nueva
            Schema::create('auditorias', function (Blueprint $table) {
                $table->id();
                $table->string('nombre_auditoria');
                $table->enum('tipo_auditoria', ['Interna', 'Externa']);
                $table->string('auditor_lider');
                $table->date('fecha_inicio');
                $table->date('fecha_fin');
                $table->year('anio');
                $table->text('auditores')->nullable();
                $table->string('archivo_path')->nullable();
                $table->string('archivo_nombre')->nullable();
                $table->string('status')->default('activo');
                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::table('auditorias', function (Blueprint $table) {
            $table->dropColumn(['fecha_inicio', 'fecha_fin', 'deleted_at', 'status']);
        });
    }
};
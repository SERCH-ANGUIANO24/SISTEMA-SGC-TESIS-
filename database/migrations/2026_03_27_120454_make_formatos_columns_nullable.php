<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formatos', function (Blueprint $table) {
            $table->string('proceso', 255)->nullable()->change();
            $table->string('departamento', 255)->nullable()->change();
            $table->string('codigo_procedimiento', 255)->nullable()->change();
            $table->string('version_procedimiento', 255)->nullable()->change();
            $table->string('nombre_archivo', 255)->nullable()->change();
            $table->string('ruta_archivo', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('formatos', function (Blueprint $table) {
            $table->string('proceso', 255)->nullable(false)->change();
            $table->string('departamento', 255)->nullable(false)->change();
            $table->string('codigo_procedimiento', 255)->nullable(false)->change();
            $table->string('version_procedimiento', 255)->nullable(false)->change();
            $table->string('nombre_archivo', 255)->nullable(false)->change();
            $table->string('ruta_archivo', 255)->nullable(false)->change();
        });
    }
};
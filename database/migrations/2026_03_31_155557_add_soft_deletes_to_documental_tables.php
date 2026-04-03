<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar soft deletes a documental_documents
        Schema::table('documental_documents', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Agregar soft deletes a documental_folders
        Schema::table('documental_folders', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Agregar soft deletes a formatos (Lista Maestra)
        Schema::table('formatos', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('documental_documents', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('documental_folders', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('formatos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
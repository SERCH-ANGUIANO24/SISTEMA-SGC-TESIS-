<?php
// database/migrations/2024_01_01_000006_create_documental_documents_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documental_documents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('original_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('extension')->nullable();
            $table->foreignId('folder_id')->nullable()->constrained('documental_folders')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Campos específicos de Gestión Documental
            $table->string('responsable')->nullable();
            $table->string('proceso')->nullable();
            $table->string('departamento')->nullable();
            $table->enum('estatus', ['Valido', 'No Valido'])->default('No Valido');
            $table->text('observaciones')->nullable();
            $table->date('fecha')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documental_documents');
    }
};
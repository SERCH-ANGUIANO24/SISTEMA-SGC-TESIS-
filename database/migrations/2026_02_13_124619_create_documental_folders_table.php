<?php
// database/migrations/2024_01_01_000005_create_documental_folders_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documental_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->default('#800000');
            $table->foreignId('parent_id')->nullable()->constrained('documental_folders')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documental_folders');
    }
};
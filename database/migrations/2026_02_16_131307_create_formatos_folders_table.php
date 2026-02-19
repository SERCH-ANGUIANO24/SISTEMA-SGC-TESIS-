<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formatos_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->default('#16a34a');
            $table->foreignId('parent_id')->nullable()->constrained('formatos_folders')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formatos_folders');
    }
};
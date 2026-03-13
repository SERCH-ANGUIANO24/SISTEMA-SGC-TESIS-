<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procesos_departamentos', function (Blueprint $table) {
            $table->id();
            $table->string('proceso');
            $table->string('departamento');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procesos_departamentos');
    }
};
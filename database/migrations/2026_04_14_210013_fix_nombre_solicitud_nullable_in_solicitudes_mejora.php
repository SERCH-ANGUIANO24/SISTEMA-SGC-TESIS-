<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_mejora', function (Blueprint $table) {
            $table->string('nombre_solicitud')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_mejora', function (Blueprint $table) {
            $table->string('nombre_solicitud')->nullable(false)->change();
        });
    }
};
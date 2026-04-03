<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('solicitudes_mejora', function (Blueprint $table) {
            $table->date('fecha_solicitud')->nullable()->change();
            $table->string('responsable_accion')->nullable()->change();
            $table->date('fecha_aplicacion')->nullable()->change();

        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_mejora', function (Blueprint $table) {
            $table->date('fecha_solicitud')->nullable(false)->change();
            $table->string('responsable_accion')->nullable(false)->change();
            $table->date('fecha_aplicacion')->nullable(false)->change();
        });
    }
};
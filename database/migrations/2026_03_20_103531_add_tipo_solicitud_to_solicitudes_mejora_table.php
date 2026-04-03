<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_mejora', function (Blueprint $table) {
            $table->string('tipo_solicitud')->nullable()->after('procesos_auditados');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_mejora', function (Blueprint $table) {
            $table->dropColumn('tipo_solicitud');
        });
    }
};
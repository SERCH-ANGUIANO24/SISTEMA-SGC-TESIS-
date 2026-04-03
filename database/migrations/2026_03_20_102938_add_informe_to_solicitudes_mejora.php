<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_mejora', function (Blueprint $table) {
            $table->unsignedBigInteger('informe_id')->nullable()->after('archivo_ruta');
            $table->date('fecha_informe')->nullable()->after('informe_id');

            $table->foreign('informe_id')
                  ->references('id')
                  ->on('informes_auditoria')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_mejora', function (Blueprint $table) {
            $table->dropForeign(['informe_id']);
            $table->dropColumn(['informe_id', 'fecha_informe']);
        });
    }
};
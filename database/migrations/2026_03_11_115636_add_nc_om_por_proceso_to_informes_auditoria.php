<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('informes_auditoria', function (Blueprint $table) {
            // Guarda un JSON: [{"proceso":"Planeación","nc":2,"om":1}, ...]
            $table->json('nc_om_por_proceso')->nullable()->after('oportunidades_mejora');
        });
    }

    public function down(): void
    {
        Schema::table('informes_auditoria', function (Blueprint $table) {
            $table->dropColumn('nc_om_por_proceso');
        });
    }
};
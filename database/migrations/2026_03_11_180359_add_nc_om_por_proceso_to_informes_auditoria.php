<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('informes_auditoria', function (Blueprint $table) {
            // Solo agregar fecha_inicio y fecha_fin si no existen
            if (!Schema::hasColumn('informes_auditoria', 'fecha_inicio')) {
                $table->date('fecha_inicio')->nullable()->after('fecha_auditoria');
            }
            if (!Schema::hasColumn('informes_auditoria', 'fecha_fin')) {
                $table->date('fecha_fin')->nullable()->after('fecha_inicio');
            }
            // Solo agregar nc_om_por_proceso si no existe
            if (!Schema::hasColumn('informes_auditoria', 'nc_om_por_proceso')) {
                $table->json('nc_om_por_proceso')->nullable()->after('oportunidades_mejora');
            }
        });
    }

    public function down(): void
    {
        Schema::table('informes_auditoria', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('informes_auditoria', 'fecha_inicio'))     $cols[] = 'fecha_inicio';
            if (Schema::hasColumn('informes_auditoria', 'fecha_fin'))        $cols[] = 'fecha_fin';
            if (Schema::hasColumn('informes_auditoria', 'nc_om_por_proceso')) $cols[] = 'nc_om_por_proceso';
            if (!empty($cols)) $table->dropColumn($cols);
        });
    }
};
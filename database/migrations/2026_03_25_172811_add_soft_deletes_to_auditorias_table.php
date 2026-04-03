<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('auditorias', function (Blueprint $table) {
            if (!Schema::hasColumn('auditorias', 'deleted_at')) {
                $table->softDeletes();
            }
            
            if (!Schema::hasColumn('auditorias', 'fecha_inicio')) {
                $table->date('fecha_inicio')->nullable()->after('auditor_lider');
            }
            
            if (!Schema::hasColumn('auditorias', 'fecha_fin')) {
                $table->date('fecha_fin')->nullable()->after('fecha_inicio');
            }
        });
    }

    public function down()
    {
        Schema::table('auditorias', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['fecha_inicio', 'fecha_fin']);
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('informes_auditoria', function (Blueprint $table) {
            if (!Schema::hasColumn('informes_auditoria', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('informes_auditoria', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
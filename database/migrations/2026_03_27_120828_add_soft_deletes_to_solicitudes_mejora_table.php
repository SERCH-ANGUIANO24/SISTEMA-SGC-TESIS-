<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_mejora', function (Blueprint $table) {
            if (!Schema::hasColumn('solicitudes_mejora', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes_mejora', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
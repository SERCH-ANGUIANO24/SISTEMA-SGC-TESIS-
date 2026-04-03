<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documental_documents', function (Blueprint $table) {
            // 'Formato' | 'Procedimiento' | null (documentos de admin no lo usan)
            $table->string('tipo_documento')->nullable()->after('version_procedimiento');
        });
    }

    public function down(): void
    {
        Schema::table('documental_documents', function (Blueprint $table) {
            $table->dropColumn('tipo_documento');
        });
    }
};
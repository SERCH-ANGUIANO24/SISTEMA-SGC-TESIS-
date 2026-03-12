<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documental_documents', function (Blueprint $table) {
            $table->string('clave_formato')->nullable()->after('departamento');
            $table->string('codigo_procedimiento')->nullable()->after('clave_formato');
            $table->string('version_procedimiento')->nullable()->after('codigo_procedimiento');
        });
    }

    public function down(): void
    {
        Schema::table('documental_documents', function (Blueprint $table) {
            $table->dropColumn(['clave_formato', 'codigo_procedimiento', 'version_procedimiento']);
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('formatos', function (Blueprint $table) {
            // Hacer nullable clave_formato para que los Procedimientos puedan no tenerla
            $table->string('clave_formato', 100)->nullable()->change();
            // Tipo: 'Formato' | 'Procedimiento' | null (subidos directamente desde Lista Maestra)
            $table->string('tipo_documento')->nullable()->after('tamanio_archivo');
        });
    }

    public function down(): void
    {
        Schema::table('formatos', function (Blueprint $table) {
            $table->string('clave_formato', 100)->nullable(false)->change();
            $table->dropColumn('tipo_documento');
        });
    }
};
<?php
// database/migrations/2024_01_01_000001_create_auditorias_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('auditorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_auditoria');
            $table->enum('tipo_auditoria', ['Interna', 'Externa']);
            $table->string('auditor_lider');
            $table->date('fecha_auditoria');
            $table->year('anio');
            $table->text('auditores')->nullable();
            $table->string('archivo_path')->nullable();
            $table->string('archivo_nombre')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('auditorias');
    }
};
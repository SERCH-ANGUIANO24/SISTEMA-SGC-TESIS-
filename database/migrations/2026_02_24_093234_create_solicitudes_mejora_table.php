<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('solicitudes_mejora', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_solicitud');
            $table->enum('tipo_auditoria', ['Interna', 'Externa']);
            $table->string('folio_solicitud')->unique();
            $table->string('responsable_accion');
            $table->date('fecha_solicitud');
            $table->date('fecha_aplicacion');
            $table->text('actividades_verificacion');
            $table->date('fecha_verificacion');
            $table->enum('estatus', ['Cerrado', 'En Proceso']);
            $table->string('archivo_path');
            $table->string('archivo_nombre');
            $table->foreignId('auditoria_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('solicitudes_mejora');
    }
};
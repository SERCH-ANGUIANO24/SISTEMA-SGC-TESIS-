<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFoldersTable extends Migration
{
    public function up()
    {
        Schema::create('folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->default('#808080'); // Color hexadecimal
            $table->foreignId('parent_id')->nullable()->constrained('folders')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Opcional: dueño
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('folders');
    }
}
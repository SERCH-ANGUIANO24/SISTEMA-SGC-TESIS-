<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE `documental_documents`
            MODIFY COLUMN `estatus` ENUM('Pendiente', 'Valido', 'No Valido') DEFAULT 'Pendiente'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE `documental_documents`
            MODIFY COLUMN `estatus` VARCHAR(255) NULL
        ");
    }
};
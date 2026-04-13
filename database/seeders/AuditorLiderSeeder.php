<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuditorLiderSeeder extends Seeder
{
    public function run(): void
    {
        // ── AUDITOR LÍDER ──────────────────────────────────
        User::updateOrCreate(
            ['email' => 'auditor.lider@uptex.edu.mx'],
            [
                'name'         => 'Auditor Líder',
                'password'     => Hash::make('AuditorLider2026!'),
                'role'         => 'auditor_lider',
                'is_active'    => true,
                'proceso'      => 'Sistema de Gestión de la Calidad',
                'departamento' => 'Auditoría',
            ]
        );
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // ── SUPERADMINISTRADOR ─────────────────────────────
        User::updateOrCreate(
            ['email' => 'superadmin@uptex.edu.mx'],
            [
                'name'       => 'Super Administrador',
                'password'   => Hash::make('SuperAdmin2026!'),
                'role'       => 'superadmin',
                'is_active'  => true,
                'proceso'    => 'TI',
                'departamento' => 'Sistemas Computacionales',
            ]
        );

        // ── ADMINISTRADOR ──────────────────────────────────
        User::updateOrCreate(
            ['email' => 'admin@uptex.edu.mx'],
            [
                'name'       => 'Administrador',
                'password'   => Hash::make('Admin2026!'),
                'role'       => 'admin',
                'is_active'  => true,
                'proceso'    => 'TI',
                'departamento' => 'Sistemas Computacionales',
            ]
        );
    }
}
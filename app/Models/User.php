<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// MODELO DEL USUARIO DEL SISTEMA
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // CAMPOS QUE SE PUEDEN GUARDAR EN LA BASE DE DATOS
    protected $fillable = [
        'name',         // NOMBRE DEL USUARIO
        'email',        // CORREO ELECTRÓNICO
        'password',     // CONTRASEÑA
        'role',         // ROL DEL USUARIO (superadmin, admin, user, auditor_lider)
        'is_active',    // SI EL USUARIO ESTÁ ACTIVO O NO
        'proceso',      // PROCESO AL QUE PERTENECE
        'departamento', // DEPARTAMENTO AL QUE PERTENECE
        'theme_color',  // COLOR DE TEMA DE LA INTERFAZ
    ];

    // CAMPOS QUE NUNCA SE MUESTRAN EN RESPUESTAS JSON
    protected $hidden = [
        'password',       // SE OCULTA POR SEGURIDAD
        'remember_token', // SE OCULTA POR SEGURIDAD
    ];

    // CONVERSIÓN AUTOMÁTICA DE TIPOS AL LEER LOS CAMPOS
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime', // SE LEE COMO FECHA
            'password'          => 'hashed',   // SE GUARDA SIEMPRE ENCRIPTADO
            'is_active'         => 'boolean',  // SE LEE COMO true O false
        ];
    }

    // ── Helpers de rol ────────────────────────────────────

    // RETORNA true SI EL USUARIO ES SUPERADMIN
    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    // RETORNA true SI EL USUARIO ES ADMIN
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // RETORNA true SI EL USUARIO ES USER (USUARIO REGULAR)
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    // RETORNA true SI EL USUARIO ES AUDITOR LÍDER
    public function isAuditorLider(): bool
    {
        return $this->role === 'auditor_lider';
    }
    // RETORNA true SI EL USUARIO PUEDE ADMINISTRAR AUDITORÍAS
    // (APLICA PARA: superadmin, admin Y auditor_lider)
    public function isAdminAuditoria(): bool
    {
        return in_array($this->role, ['superadmin', 'admin', 'auditor_lider']);
    }
}
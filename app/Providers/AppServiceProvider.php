<?php

namespace App\Providers;

use App\Services\BrevoService;
use App\Services\NotificacionService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

// PROVEEDOR PRINCIPAL DE SERVICIOS DE LA APLICACIÓN
// AQUÍ SE REGISTRAN SERVICIOS Y SE CONFIGURAN COMPORTAMIENTOS GLOBALES AL ARRANCAR LA APP
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    // REGISTRA LOS SERVICIOS EN EL CONTENEDOR DE DEPENDENCIAS DE LARAVEL
    public function register(): void
    {
        // Servicios del módulo de notificaciones
        // REGISTRA BrevoService COMO SINGLETON (UNA SOLA INSTANCIA EN TODA LA APP)
        $this->app->singleton(BrevoService::class);
        // REGISTRA NotificacionService COMO SINGLETON (UNA SOLA INSTANCIA EN TODA LA APP)
        $this->app->singleton(NotificacionService::class);

    }

    /**
     * Bootstrap any application services.
     */
    // SE EJECUTA DESPUÉS DE REGISTRAR TODOS LOS SERVICIOS
    // AQUÍ SE DEFINEN LOS GATES DE ACCESO Y SE APLICAN LAS CONFIGURACIONES GLOBALES
    public function boot(): void
    {
        // APLICA LAS CONFIGURACIONES GLOBALES DE FECHAS, BASE DE DATOS Y CONTRASEÑAS
        $this->configureDefaults();

        // Gate para proteger rutas de administrador
        // SOLO USUARIOS CON ROL superadmin O admin PUEDEN PASAR ESTE GATE
        Gate::define('admin-access', function ($user) {
            return in_array($user->role, ['superadmin', 'admin']);
        });

        // Gate para módulo de auditorías — incluye auditor_lider
        // USUARIOS CON ROL superadmin, admin O auditor_lider PUEDEN ACCEDER AL MÓDULO DE AUDITORÍAS
        Gate::define('auditoria-access', function ($user) {
            return in_array($user->role, ['superadmin', 'admin', 'auditor_lider']);
        });
    }

    // CONFIGURACIONES GLOBALES QUE SE APLICAN AL INICIAR LA APLICACIÓN
    protected function configureDefaults(): void
    {
        // USA CarbonImmutable COMO CLASE DE FECHAS (LAS FECHAS NO SE MODIFICAN, SE CREAN NUEVAS)
        Date::use(CarbonImmutable::class);

        // EN PRODUCCIÓN, PROHÍBE COMANDOS DESTRUCTIVOS EN LA BASE DE DATOS (DROP, TRUNCATE, ETC.)
        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        // DEFINE LAS REGLAS DE CONTRASEÑA POR DEFECTO
        // EN PRODUCCIÓN: MÍNIMO 12 CARACTERES, MAYÚSCULAS, MINÚSCULAS, NÚMEROS, SÍMBOLOS Y NO COMPROMETIDA
        // EN DESARROLLO: SIN RESTRICCIONES (null)
        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }
}
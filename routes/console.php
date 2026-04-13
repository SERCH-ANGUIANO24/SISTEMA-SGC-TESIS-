<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| ARCHIVO: CONSOLE.PHP
|--------------------------------------------------------------------------
| AQUÍ SE REGISTRAN LOS COMANDOS DE CONSOLA Y LAS TAREAS
| PROGRAMADAS (SCHEDULER) DE LA APLICACIÓN.
*/

// ============================================================
// COMANDO: inspire
// MUESTRA UNA FRASE INSPIRADORA EN LA CONSOLA.
// SE EJECUTA CON: php artisan inspire
// ============================================================
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ============================================================
// TAREA PROGRAMADA: solicitudes:recordatorio
// ENVÍA RECORDATORIOS DE SOLICITUDES DE MEJORA PENDIENTES.
// SE EJECUTA AUTOMÁTICAMENTE TODOS LOS DÍAS A LAS 08:00 AM.
// ============================================================
Schedule::command('solicitudes:recordatorio')->dailyAt('08:00');
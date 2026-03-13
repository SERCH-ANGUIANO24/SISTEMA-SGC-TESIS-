<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\AnexosController;
use App\Http\Controllers\DocumentalController;
use App\Http\Controllers\MatrizController;
use App\Http\Controllers\FormatoController;
use App\Http\Controllers\Admin\UsuariosController;
use App\Http\Controllers\Admin\ProcesoController;   
use App\Http\Controllers\HistorialVersionesController;

// ===== CONTROLADORES DE AUDITORÍA =====
use App\Http\Controllers\Auditoria\AuditoriaController;
use App\Http\Controllers\Auditoria\PlanAuditoriaController;
use App\Http\Controllers\Auditoria\InformeAuditoriaController;
use App\Http\Controllers\Auditoria\SolicitudMejoraController;
use App\Http\Controllers\Auditoria\CompetenciaController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Dashboard (protegido)
Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

/* ===== MÓDULO ANEXOS ===== */
Route::middleware(['auth'])->group(function () {
    Route::prefix('anexos')->name('anexos.')->group(function () {
        Route::get('/', [AnexosController::class, 'index'])->name('index');
        Route::post('/folder', [AnexosController::class, 'storeFolder'])->name('folder.store');
        Route::post('/upload', [AnexosController::class, 'uploadDocument'])->name('upload');
        Route::delete('/folder/{id}', [AnexosController::class, 'destroyFolder'])->name('folder.destroy');
        Route::delete('/document/{id}', [AnexosController::class, 'destroyDocument'])->name('document.destroy');
        Route::get('/document/{id}/download', [AnexosController::class, 'downloadDocument'])->name('document.download');
        Route::get('/document/{id}/view', [AnexosController::class, 'viewDocument'])->name('document.view');
        Route::put('/folder/{id}/rename', [AnexosController::class, 'renameFolder'])->name('folder.rename');
        Route::put('/folder/{id}/move', [AnexosController::class, 'moveFolder'])->name('folder.move');
        Route::get('/folders/tree', [AnexosController::class, 'getFoldersTree'])->name('folders.tree');
        Route::put('/document/{id}/rename', [AnexosController::class, 'renameDocument'])->name('document.rename');
        Route::put('/document/{id}/move', [AnexosController::class, 'moveDocument'])->name('document.move');
        Route::get('/ver-archivo/{id}', [AnexosController::class, 'viewDocument'])->name('ver.archivo');
    });
});

/* ===== MÓDULO GESTIÓN DOCUMENTAL ===== */
Route::middleware(['auth'])->group(function () {
    Route::prefix('documental')->name('documental.')->group(function () {
        Route::get('/', [DocumentalController::class, 'index'])->name('index');
        Route::post('/folder', [DocumentalController::class, 'storeFolder'])->name('folder.store');
        Route::put('/folder/{id}', [DocumentalController::class, 'updateFolder'])->name('folder.update');
        Route::delete('/folder/{id}', [DocumentalController::class, 'destroyFolder'])->name('folder.destroy');
        Route::put('/folder/{id}/rename', [DocumentalController::class, 'renameFolder'])->name('folder.rename');
        Route::put('/folder/{id}/move', [DocumentalController::class, 'moveFolder'])->name('folder.move');
        Route::post('/upload', [DocumentalController::class, 'upload'])->name('upload');
        Route::get('/document/{id}/data', [DocumentalController::class, 'getDocumentData'])->name('document.data');
        Route::put('/document/{id}', [DocumentalController::class, 'updateDocument'])->name('document.update');
        Route::put('/document/{id}/move', [DocumentalController::class, 'moveDocument'])->name('document.move');
        Route::get('/document/{id}/download', [DocumentalController::class, 'downloadDocument'])->name('document.download');
        Route::get('/document/{id}/view', [DocumentalController::class, 'viewDocument'])->name('document.view');
        Route::get('/ver-archivo/{id}', [DocumentalController::class, 'viewDocument'])->name('ver.archivo');
        Route::delete('/document/{id}', [DocumentalController::class, 'destroyDocument'])->name('document.destroy');
        Route::get('/folders/tree', [DocumentalController::class, 'getFoldersTree'])->name('folders.tree');
    });
});

/* ===== MÓDULO MATRIZ ===== */
Route::middleware(['auth'])->group(function () {
    Route::prefix('matriz')->name('matriz.')->group(function () {
        Route::get('/', [MatrizController::class, 'index'])->name('index');
        Route::post('/folder', [MatrizController::class, 'storeFolder'])->name('folder.store');
        Route::put('/folder/{id}/rename', [MatrizController::class, 'renameFolder'])->name('folder.rename');
        Route::put('/folder/{id}/move', [MatrizController::class, 'moveFolder'])->name('folder.move');
        Route::delete('/folder/{id}', [MatrizController::class, 'destroyFolder'])->name('folder.destroy');
        Route::post('/upload', [MatrizController::class, 'upload'])->name('upload');
        Route::get('/document/{id}/data', [MatrizController::class, 'getDocumentData'])->name('document.data');
        Route::put('/document/{id}', [MatrizController::class, 'updateDocument'])->name('document.update');
        Route::put('/document/{id}/move', [MatrizController::class, 'moveDocument'])->name('document.move');
        Route::get('/document/{id}/download', [MatrizController::class, 'downloadDocument'])->name('document.download');
        Route::get('/document/{id}/view', [MatrizController::class, 'viewDocument'])->name('document.view');
        Route::get('/ver-archivo/{id}', [MatrizController::class, 'viewDocument'])->name('ver.archivo');
        Route::delete('/document/{id}', [MatrizController::class, 'destroyDocument'])->name('document.destroy');
        Route::get('/folders/tree', [MatrizController::class, 'getFoldersTree'])->name('folders.tree');
    });
});

/* ===== MÓDULO FORMATOS ===== */
Route::middleware(['auth'])->group(function () {
    Route::prefix('formatos')->name('formatos.')->group(function () {
        Route::get('/',  [FormatoController::class, 'index'])->name('index');
        Route::post('/', [FormatoController::class, 'store'])->name('store');
        Route::get('/api/departamentos', [FormatoController::class, 'departamentos'])->name('departamentos');
        Route::get('/procesos-departamentos',          [ProcesosDepartamentosController::class, 'index'])->name('procesos-departamentos.index');
        Route::post('/procesos-departamentos',         [ProcesosDepartamentosController::class, 'store'])->name('procesos-departamentos.store');
        Route::delete('/procesos-departamentos',       [ProcesosDepartamentosController::class, 'destroy'])->name('procesos-departamentos.destroy');
        Route::delete('/procesos-departamentos/depto', [ProcesosDepartamentosController::class, 'destroyDepartamento'])->name('procesos-departamentos.destroyDepto');
        Route::get('/{formato}/ver',        [FormatoController::class, 'show'])->name('show');
        Route::get('/{formato}/descargar',  [FormatoController::class, 'download'])->name('download');
        Route::put('/{formato}',            [FormatoController::class, 'update'])->name('update');
        Route::delete('/{formato}',         [FormatoController::class, 'destroy'])->name('destroy');
    });
});


/* ===== MÓDULO ADMINISTRACIÓN DE USUARIOS ===== */
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::prefix('usuarios')->name('usuarios.')->group(function () {
        Route::get('/',                   [UsuariosController::class, 'index'])->name('index');
        Route::patch('/{usuario}/estado', [UsuariosController::class, 'estado'])->name('estado');
        Route::delete('/{usuario}',       [UsuariosController::class, 'destroy'])->name('destroy');
        Route::patch('/{usuario}/admin',  [UsuariosController::class, 'updateAdmin'])->name('updateAdmin');
    });
    Route::prefix('procesos')->name('procesos.')->group(function () {
        Route::post('/',                     [ProcesoController::class, 'store'])->name('store');
        Route::post('/add-departamento',     [ProcesoController::class, 'addDepartamento'])->name('addDepartamento');
        Route::delete('/destroy-proceso',    [ProcesoController::class, 'destroyProceso'])->name('destroyProceso');
        Route::delete('/{proceso}',          [ProcesoController::class, 'destroy'])->name('destroy');
    });
});

/* ===== MÓDULO HISTORIAL DE VERSIONES ===== */
Route::middleware(['auth'])->group(function () {
    Route::prefix('historial-versiones')->name('historial-versiones.')->group(function () {
        Route::get('/', [HistorialVersionesController::class, 'index'])->name('index');
        Route::get('/mis-actividades', [HistorialVersionesController::class, 'misActividades'])->name('mis-actividades');
        Route::get('/datos/graficos', [HistorialVersionesController::class, 'datosGraficos'])->name('datos-graficos');
        Route::get('/exportar', [HistorialVersionesController::class, 'exportar'])->name('exportar');
        Route::delete('/limpiar', [HistorialVersionesController::class, 'limpiar'])->name('limpiar');
        Route::post('/restaurar/{id}', [HistorialVersionesController::class, 'restaurar'])->name('restaurar'); // Nueva ruta
        Route::get('/{id}', [HistorialVersionesController::class, 'show'])->name('show');
    });
});

/* ===== MÓDULO AUDITORÍAS ===== */
Route::middleware(['auth'])->group(function () {
    Route::get('/auditoria', function () {
        return view('auditoria.dashboard');
    })->name('auditoria.dashboard');

    Route::prefix('auditoria/plan')->name('auditoria.plan.')->group(function () {
        Route::get('/', [PlanAuditoriaController::class, 'index'])->name('index');
        Route::get('/data', [AuditoriaController::class, 'getData'])->name('data');
        Route::post('/', [AuditoriaController::class, 'store'])->name('store');
        Route::put('/{id}', [AuditoriaController::class, 'update'])->name('update');
        Route::delete('/{id}', [AuditoriaController::class, 'destroy'])->name('destroy');
        Route::get('/download/{id}', [AuditoriaController::class, 'download'])->name('download');
        Route::get('/ver/{id}', [AuditoriaController::class, 'verArchivo'])->name('ver');
        Route::get('/chart-data', [AuditoriaController::class, 'getChartData'])->name('chart.data');
        Route::get('/calendar-events', [PlanAuditoriaController::class, 'getCalendarEvents'])->name('calendar.events');
    });

    // SUBMODULO DE INFORMES
    Route::prefix('auditorias/informes')->name('informes-auditoria.')->middleware(['auth'])->group(function () {
        Route::get('/estadisticas/por-anio', [InformeAuditoriaController::class, 'estadisticasPorAnio'])->name('estadisticas');
        Route::get('/auditoria/{auditoria}/fecha', [InformeAuditoriaController::class, 'fechaAuditoriaRelacionada'])->name('fecha-auditoria');
        Route::get('/', [InformeAuditoriaController::class, 'index'])->name('index');
        Route::post('/', [InformeAuditoriaController::class, 'store'])->name('store');
        Route::get('/procesos-custom', [InformeAuditoriaController::class, 'getProcesosCustom'])->name('procesos-custom');
        Route::get('/{informeAuditoria}', [InformeAuditoriaController::class, 'show'])->name('show');
        Route::post('/{informeAuditoria}', [InformeAuditoriaController::class, 'update'])->name('update');
        Route::put('/{informeAuditoria}', [InformeAuditoriaController::class, 'update'])->name('update-put');
        Route::delete('/{informeAuditoria}', [InformeAuditoriaController::class, 'destroy'])->name('destroy');
        Route::get('/{informeAuditoria}/grafica', [InformeAuditoriaController::class, 'graficaInforme'])->name('grafica');
        Route::get('/{informeAuditoria}/documento', [InformeAuditoriaController::class, 'verDocumento'])->name('documento');
        Route::get('/{informeAuditoria}/descargar', [InformeAuditoriaController::class, 'descargar'])->name('descargar');
    });

    // ===== RUTAS DE SOLICITUD DE MEJORA =====
    Route::prefix('auditoria/solicitudes')->name('auditoria.solicitudes.')->group(function () {
        Route::get('/', [SolicitudMejoraController::class, 'index'])->name('index');
        Route::get('/data', [SolicitudMejoraController::class, 'data'])->name('data');
        Route::post('/', [SolicitudMejoraController::class, 'store'])->name('store');
        Route::put('/{id}', [SolicitudMejoraController::class, 'update'])->name('update');
        Route::delete('/{id}', [SolicitudMejoraController::class, 'destroy'])->name('destroy');
        Route::get('/ver/{id}', [SolicitudMejoraController::class, 'view'])->name('view');
        Route::get('/download/{id}', [SolicitudMejoraController::class, 'download'])->name('download');
    });

    /* ===== MÓDULO COMPETENCIAS ===== */
    Route::prefix('auditoria/competencias')->name('auditoria.competencias.')->group(function () {
        Route::get('/', [CompetenciaController::class, 'index'])->name('index');
        Route::get('/folder/{folder}', [CompetenciaController::class, 'index'])->name('folder.show');
        Route::post('/folder', [CompetenciaController::class, 'storeFolder'])->name('folder.store');
        Route::put('/folder/{id}/rename', [CompetenciaController::class, 'renameFolder'])->name('folder.rename');
        Route::put('/folder/{id}/move', [CompetenciaController::class, 'moveFolder'])->name('folder.move');
        Route::delete('/folder/{id}', [CompetenciaController::class, 'destroyFolder'])->name('folder.destroy');
        Route::post('/upload', [CompetenciaController::class, 'uploadDocument'])->name('upload');
        Route::get('/document/{id}/download', [CompetenciaController::class, 'downloadDocument'])->name('document.download');
        Route::get('/document/{id}/ver', [CompetenciaController::class, 'viewDocument'])->name('document.ver');
        Route::put('/document/{id}/rename', [CompetenciaController::class, 'renameDocument'])->name('document.rename');
        Route::put('/document/{id}/move', [CompetenciaController::class, 'moveDocument'])->name('document.move');
        Route::delete('/document/{id}', [CompetenciaController::class, 'destroyDocument'])->name('document.destroy');
        Route::get('/folders/tree', [CompetenciaController::class, 'getFoldersTree'])->name('folders.tree');
        Route::get('/document/{id}/data', [CompetenciaController::class, 'getDocumentData'])->name('document.data');
    });
});

/* ===== RUTAS DE AUTENTICACIÓN ===== */
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);
});

Route::post('logout', [LoginController::class, 'logout'])->name('logout');

require __DIR__.'/settings.php';
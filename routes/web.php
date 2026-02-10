<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\AnexosController; // ← NUEVO: Controlador de Anexos

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
    });
});
/* ======================== */

// Rutas de autenticación MANUALES (sin laravel/ui)
Route::middleware('guest')->group(function () {
    // Login
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    
    // Registro
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);
    
    // Recuperación de contraseña
    Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Logout
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

require __DIR__.'/settings.php';
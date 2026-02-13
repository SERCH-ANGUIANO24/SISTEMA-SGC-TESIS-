<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\AnexosController;
use App\Http\Controllers\DocumentalController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Dashboard
Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

/* ===== MÓDULO ANEXOS (TABLAS: folders, documents) ===== */
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
    });
});

/* ===== MÓDULO GESTIÓN DOCUMENTAL (TABLAS: documental_folders, documental_documents) ===== */
Route::middleware(['auth'])->group(function () {
    Route::prefix('documental')->name('documental.')->group(function () {
        Route::get('/', [DocumentalController::class, 'index'])->name('index');
        Route::post('/folder', [DocumentalController::class, 'storeFolder'])->name('folder.store');
        Route::post('/upload', [DocumentalController::class, 'upload'])->name('upload');
        Route::get('/document/{id}/data', [DocumentalController::class, 'getDocumentData'])->name('document.data');
        Route::put('/document/{id}', [DocumentalController::class, 'updateDocument'])->name('document.update');
        Route::put('/document/{id}/move', [DocumentalController::class, 'moveDocument'])->name('document.move');
        Route::get('/document/{id}/download', [DocumentalController::class, 'downloadDocument'])->name('document.download');
        Route::get('/document/{id}/view', [DocumentalController::class, 'viewDocument'])->name('document.view');
        Route::delete('/document/{id}', [DocumentalController::class, 'destroyDocument'])->name('document.destroy');
        Route::get('/folders/tree', [DocumentalController::class, 'getFoldersTree'])->name('folders.tree');
        Route::delete('/folder/{id}', [DocumentalController::class, 'destroyFolder'])->name('folder.destroy');
    });
});

// Rutas de autenticación
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);
    Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::post('logout', [LoginController::class, 'logout'])->name('logout');

require __DIR__.'/settings.php';
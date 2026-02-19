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
use App\Http\Controllers\FormatosController; 

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
        
        // Rutas para mover y renombrar carpetas
        Route::put('/folder/{id}/rename', [AnexosController::class, 'renameFolder'])->name('folder.rename');
        Route::put('/folder/{id}/move', [AnexosController::class, 'moveFolder'])->name('folder.move');
        Route::get('/folders/tree', [AnexosController::class, 'getFoldersTree'])->name('folders.tree');

        // Rutas para mover y renombrar documentos
        Route::put('/document/{id}/rename', [AnexosController::class, 'renameDocument'])->name('document.rename');
        Route::put('/document/{id}/move', [AnexosController::class, 'moveDocument'])->name('document.move');
    });
});

/* ===== MÓDULO GESTIÓN DOCUMENTAL ===== */
Route::middleware(['auth'])->group(function () {
    Route::prefix('documental')->name('documental.')->group(function () {
        // Vista principal
        Route::get('/', [DocumentalController::class, 'index'])->name('index');
        
        // Carpetas
        Route::post('/folder', [DocumentalController::class, 'storeFolder'])->name('folder.store');
        Route::put('/folder/{id}', [DocumentalController::class, 'updateFolder'])->name('folder.update');
        Route::delete('/folder/{id}', [DocumentalController::class, 'destroyFolder'])->name('folder.destroy');

        //RUTAS PARA RENOMBRAR, MOVER Y  CARPETAS DEL MODULO DE GESTION DOCUMENTAL
        Route::put('/folder/{id}/rename', [DocumentalController::class, 'renameFolder'])->name('folder.rename');
        Route::put('/folder/{id}/move', [DocumentalController::class, 'moveFolder'])->name('folder.move'); 
        
        // Documentos
        Route::post('/upload', [DocumentalController::class, 'upload'])->name('upload');
        Route::get('/document/{id}/data', [DocumentalController::class, 'getDocumentData'])->name('document.data');
        Route::put('/document/{id}', [DocumentalController::class, 'updateDocument'])->name('document.update');
        Route::put('/document/{id}/move', [DocumentalController::class, 'moveDocument'])->name('document.move');
        Route::get('/document/{id}/download', [DocumentalController::class, 'downloadDocument'])->name('document.download');
        Route::get('/document/{id}/view', [DocumentalController::class, 'viewDocument'])->name('document.view');
        Route::delete('/document/{id}', [DocumentalController::class, 'destroyDocument'])->name('document.destroy');
        
        // Utilidades
        Route::get('/folders/tree', [DocumentalController::class, 'getFoldersTree'])->name('folders.tree');
    });
});

/* ===== MÓDULO MATRIZ ===== */
Route::middleware(['auth'])->group(function () {
    Route::prefix('matriz')->name('matriz.')->group(function () {
        // Vista principal
        Route::get('/', [MatrizController::class, 'index'])->name('index');
        
        // Carpetas
        Route::post('/folder', [MatrizController::class, 'storeFolder'])->name('folder.store');
        Route::put('/folder/{id}/rename', [MatrizController::class, 'renameFolder'])->name('folder.rename'); // 👈 AGREGADO
        Route::put('/folder/{id}/move', [MatrizController::class, 'moveFolder'])->name('folder.move');       // 👈 AGREGADO
        Route::delete('/folder/{id}', [MatrizController::class, 'destroyFolder'])->name('folder.destroy');
        
        // Documentos (matrices)
        Route::post('/upload', [MatrizController::class, 'upload'])->name('upload');
        Route::get('/document/{id}/data', [MatrizController::class, 'getDocumentData'])->name('document.data');
        Route::put('/document/{id}', [MatrizController::class, 'updateDocument'])->name('document.update');
        Route::put('/document/{id}/move', [MatrizController::class, 'moveDocument'])->name('document.move');
        Route::get('/document/{id}/download', [MatrizController::class, 'downloadDocument'])->name('document.download');
        Route::get('/document/{id}/view', [MatrizController::class, 'viewDocument'])->name('document.view');
        Route::delete('/document/{id}', [MatrizController::class, 'destroyDocument'])->name('document.destroy');
        
        // Utilidades
        Route::get('/folders/tree', [MatrizController::class, 'getFoldersTree'])->name('folders.tree');
    });
});

/* ===== MÓDULO FORMATOS ===== */
Route::middleware(['auth'])->group(function () {
    Route::prefix('formatos')->name('formatos.')->group(function () {
        // Vista principal
        Route::get('/', [FormatosController::class, 'index'])->name('index');
        
        // Volver a la raíz
        Route::get('/volver', [FormatosController::class, 'volver'])->name('volver');
        
        // Carpetas
        Route::post('/folder', [FormatosController::class, 'storeFolder'])->name('folder.store');
        Route::put('/folder/{id}/rename', [FormatosController::class, 'renameFolder'])->name('folder.rename');
        Route::put('/folder/{id}/move', [FormatosController::class, 'moveFolder'])->name('folder.move');   
        Route::delete('/folder/{id}', [FormatosController::class, 'destroyFolder'])->name('folder.destroy');
        
        // Documentos
        Route::post('/upload', [FormatosController::class, 'upload'])->name('upload');
        Route::get('/document/{id}/data', [FormatosController::class, 'getDocumentData'])->name('document.data');
        Route::put('/document/{id}', [FormatosController::class, 'updateDocument'])->name('document.update');
        Route::put('/document/{id}/move', [FormatosController::class, 'moveDocument'])->name('document.move');
        Route::get('/document/{id}/download', [FormatosController::class, 'downloadDocument'])->name('document.download');
        Route::get('/document/{id}/view', [FormatosController::class, 'viewDocument'])->name('document.view');
        Route::delete('/document/{id}', [FormatosController::class, 'destroyDocument'])->name('document.destroy');
        
        // Utilidades
        Route::get('/folders/tree', [FormatosController::class, 'getFoldersTree'])->name('folders.tree');
    });
});

// ===== RUTAS PARA VISUALIZAR ARCHIVOS =====
Route::get('/ver-imagen/{id}', function($id) {
    $document = App\Models\FormatoDocument::findOrFail($id);
    $path = storage_path('app/public/' . $document->file_path);
    
    if (!file_exists($path)) {
        abort(404);
    }
    
    return response()->file($path);
})->name('ver.imagen');

Route::get('/anexos/ver-archivo/{id}', function($id) {
    $document = App\Models\Document::findOrFail($id);
    
    // Verificar que el usuario tiene permiso
    if ($document->user_id != Auth::id()) {
        abort(403);
    }
    
    $path = storage_path('app/public/' . $document->file_path);
    
    if (!file_exists($path)) {
        abort(404);
    }
    
    return response()->file($path);
})->name('anexos.ver.archivo')->middleware('auth');

Route::get('/documental/ver-archivo/{id}', function($id) {
    $document = App\Models\DocumentalDocument::findOrFail($id);
    
    // Verificar que el usuario tiene permiso
    if ($document->user_id != Auth::id()) {
        abort(403);
    }
    
    $path = storage_path('app/public/' . $document->file_path);
    
    if (!file_exists($path)) {
        abort(404);
    }
    
    return response()->file($path);
})->name('documental.ver.archivo')->middleware('auth');

Route::get('/matriz/ver-archivo/{id}', function($id) {
    $document = App\Models\MatrizDocument::findOrFail($id);
    
    // Verificar que el usuario tiene permiso
    if ($document->user_id != Auth::id()) {
        abort(403);
    }
    
    $path = storage_path('app/public/' . $document->file_path);
    
    if (!file_exists($path)) {
        abort(404);
    }
    
    return response()->file($path);
})->name('matriz.ver.archivo')->middleware('auth');

/* ===== RUTAS DE AUTENTICACIÓN ===== */
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
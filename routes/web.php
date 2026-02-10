<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\Preturi2026Controller;
use App\Http\Controllers\TeraseController;
use App\Http\Controllers\AgentController;
use App\Http\Middleware\AuthMiddleware;

// Redirect root to login
Route::get('/', function () {
    return redirect('/login');
});

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes - require authentication
Route::middleware([AuthMiddleware::class])->group(function () {
    // Upload routes - Baza de Date Contabilitate
    Route::get('/upload', [UploadController::class, 'index'])->name('upload');
    Route::post('/upload', [UploadController::class, 'upload']);
    Route::delete('/upload/delete', [UploadController::class, 'delete'])->name('upload.delete');

    // Prețuri 2026 routes
    Route::get('/preturi-2026', [Preturi2026Controller::class, 'index'])->name('preturi2026');
    Route::post('/preturi-2026', [Preturi2026Controller::class, 'upload']);
    Route::post('/preturi-2026/update', [Preturi2026Controller::class, 'updateRow'])->name('preturi2026.update');
    Route::post('/preturi-2026/delete', [Preturi2026Controller::class, 'deleteRow'])->name('preturi2026.delete');
    Route::delete('/preturi-2026/delete-all', [Preturi2026Controller::class, 'deleteAll'])->name('preturi2026.deleteAll');

    // Terase routes
    Route::get('/terase', [TeraseController::class, 'index'])->name('terase');
    Route::post('/terase/update', [TeraseController::class, 'updateRow'])->name('terase.update');

    // Agent routes
    Route::get('/agent', [AgentController::class, 'index'])->name('agent');
    Route::post('/agent', [AgentController::class, 'ask']);
    Route::post('/agent/clear', [AgentController::class, 'clear'])->name('agent.clear');
});

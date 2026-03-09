<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\SpecialtyController; // <--- No olvides el import
use App\Http\Controllers\DoctorController;

Route::get('/', function () {
    return view('welcome');
});

// Dashboard por defecto de Jetstream (Tailwind)
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

// Grupo de tu Administración Manual (Bootstrap)
// Grupo de Administración Staff (Dashboard común)
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'role:admin|director_tecnico|contable'
])->prefix('admin')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // ESTE SUB-GRUPO ES SOLO PARA ADMIN Y DT
    Route::middleware(['role:admin|director_tecnico'])->group(function () {
        Route::resource('specialties', SpecialtyController::class);
        Route::resource('doctors', DoctorController::class); // <-- Nueva ruta
    });
});

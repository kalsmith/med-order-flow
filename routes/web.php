<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Modelos
use App\Models\Specialty;
use App\Models\ExamType;

// Controladores Administrativos
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\ExamTypeController;
use App\Http\Controllers\MedicalOrderController;

// Controladores Públicos
use App\Http\Controllers\PublicOrderController;

/*
|--------------------------------------------------------------------------
| Rutas Públicas (Kiosko & Landing)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome'); // O tu "hola mundo" actual
});

/**
 * Flujo del Kiosko: Selección de exámenes y envío de solicitudes
 */
Route::controller(PublicOrderController::class)->group(function () {
    Route::get('/pedir-orden', function () {
        $packs = ExamType::has('children')->where('is_active', true)->get();
        $individuales = ExamType::doesntHave('children')->where('is_active', true)->take(6)->get();

        return view('front.kiosko', compact('packs', 'individuales'));
    })->name('kiosko.index');

    Route::post('/enviar-pedido', 'store')->name('orders.store.public');
});


/*
|--------------------------------------------------------------------------
| Panel de Administración & Gestión Clínica
|--------------------------------------------------------------------------
| Protegido por Sanctum, Verificación y Roles de Spatie.
| El prefijo 'admin' y el nombre 'admin.' organizan el acceso.
*/

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->prefix('admin')->name('admin.')->group(function () {

    // --- ACCESO UNIVERSAL (Dashboard base) ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


    // --- MÓDULO: GESTIÓN DE SALUD (Admin & Director Técnico) ---
    // Solo personal de alto nivel puede alterar el catálogo y el staff médico.
    Route::middleware(['role:admin|director_tecnico'])->group(function () {
        Route::resource('specialties', SpecialtyController::class);
        Route::resource('doctors', DoctorController::class);
        Route::resource('exam-types', ExamTypeController::class);
    });


    // --- MÓDULO: OPERACIONES CLÍNICAS (Médicos) ---
    // El núcleo del negocio: Revisión y firma de órdenes pendientes.
    Route::middleware(['role:doctor'])->group(function () {
        // Listado de órdenes asignadas al doctor
        Route::get('/clinical-panel', [MedicalOrderController::class, 'index'])->name('doctor.panel');

        // Firma de órdenes individuales
        Route::get('/orders/{order}/sign', [MedicalOrderController::class, 'showSignForm'])->name('orders.sign.form');
        Route::post('/orders/{order}/sign', [MedicalOrderController::class, 'processSignature'])->name('orders.sign.process');
    });


    // --- MÓDULO: FINANZAS & REPORTES (Contables) ---
    Route::middleware(['role:contable|admin'])->group(function () {
        Route::get('/reports', [DashboardController::class, 'reports'])->name('reports');
        Route::get('/accounting', function() { return "Próximamente Módulo Contable"; })->name('accounting.index');
    });


    // --- MÓDULO: ÓRDENES GENERALES (Admin & Director Técnico) ---
    // Permite al administrador ver la trazabilidad completa de todas las órdenes.
    Route::middleware(['role:admin|director_tecnico'])->group(function () {
        Route::resource('orders', MedicalOrderController::class)->except(['create', 'store']);
    });

});


/*
|--------------------------------------------------------------------------
| Utilidades & APIs Internas
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'verified'])->prefix('api')->name('api.')->group(function () {
    // Selección dinámica de exámenes por especialidad
    Route::get('/specialties/{specialty}/exam-types', function (Specialty $specialty) {
        return $specialty->examTypes()
            ->where('is_active', true)
            ->get(['id', 'name', 'base_price']);
    })->name('exams.by.specialty');
});


/*
|--------------------------------------------------------------------------
| Jetstream / Profile Default
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard.jetstream');
});

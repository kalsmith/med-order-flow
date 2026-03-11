<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Controladores Staff
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\ExamTypeController;
use App\Http\Controllers\MedicalOrderController;

// Controladores Otros
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\PublicOrderController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FlowController;

/*
|--------------------------------------------------------------------------
| 1. ACCESO Y AUTENTICACIÓN (STAFF)
|--------------------------------------------------------------------------
*/

// La raíz ahora te lleva al login de staff mientras desarrollas
Route::get('/', function () {
    return redirect()->route('login');
});

// Login renombrado a "Acceso"
Route::get('/acceso', function() {
    return view('auth.login');
})->name('login');

Route::post('/logout', function() {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/acceso');
})->name('logout');

/*
|--------------------------------------------------------------------------
| 2. DISTRIBUIDOR DE TRÁFICO
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'verified'])->get('/home', function () {
    $user = Auth::user();

    // Prioridad Staff
    if ($user->hasAnyRole(['admin', 'doctor', 'director_tecnico', 'contable'])) {
        return redirect()->route('admin.panel');
    }

    // Si por error entra un paciente, lo mandamos al login por ahora
    return redirect()->route('login');
})->name('user.dispatch');

/*
|--------------------------------------------------------------------------
| 3. PANEL DE GESTIÓN (MUNDO ADMINISTRATIVO)
|--------------------------------------------------------------------------
| Reemplazamos 'dashboard' por 'panel' y prefijo 'gestion'
*/
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'role:admin|doctor|director_tecnico|contable'
])->prefix('gestion')->name('admin.')->group(function () {

    // Panel Principal (Sustituye al Dashboard)
    Route::get('/panel', [DashboardController::class, 'index'])->name('panel');

    // Gestión para Doctores (Panel Clínico)
    Route::middleware(['role:doctor'])->group(function () {
        Route::get('/clinico', [MedicalOrderController::class, 'index'])->name('doctor.panel');
        Route::get('/ordenes/{order}/revisar', [MedicalOrderController::class, 'showSignForm'])->name('orders.sign.form');
        Route::post('/ordenes/{order}/firmar', [MedicalOrderController::class, 'processSignature'])->name('orders.sign.process');
        Route::post('/ordenes/{order}/rechazar', [MedicalOrderController::class, 'rejectCustomOrder'])->name('orders.reject');
    });

    // Mantenedores de Configuración (Admin / Director Técnico)
    Route::middleware(['role:admin|director_tecnico'])->group(function () {
        Route::resource('especialidades', SpecialtyController::class)->names('specialties');
        Route::resource('medicos', DoctorController::class)->names('doctors');
        Route::resource('examenes', ExamTypeController::class)->names('exam-types');
        Route::resource('ordenes', MedicalOrderController::class)->names('orders')->except(['create', 'store']);
    });

    // Área Contable
    Route::middleware(['role:contable|admin'])->group(function () {
        Route::get('/reportes', [DashboardController::class, 'reports'])->name('reports');
        Route::get('/contabilidad', [DashboardController::class, 'reports'])->name('accounting.index');
    });
});

/*
|--------------------------------------------------------------------------
| 4. PASARELAS DE PAGO (FLOW WEBHOOKS)
|--------------------------------------------------------------------------
*/
Route::prefix('payment/flow')->group(function () {
    Route::match(['get', 'post'], '/return', [FlowController::class, 'returnUrl'])->name('flow.return');
    Route::post('/confirmation', [FlowController::class, 'confirmation'])->name('flow.webhook');
    Route::post('/refund-confirmation', [FlowController::class, 'refundConfirmation'])->name('flow.refund.webhook');
});

/*
|--------------------------------------------------------------------------
| BLOQUE COMENTADO: PACIENTES & GOOGLE (DEPRECADO TEMPORALMENTE)
|--------------------------------------------------------------------------
/*
Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/orden-personalizada', [PublicOrderController::class, 'customOrder'])->name('orders.custom');
    Route::get('/completar-perfil', [PublicOrderController::class, 'completeProfileForm'])->name('profile.complete');
    // ... resto de rutas de pacientes ...
});
*/

/*
|--------------------------------------------------------------------------
| 5. APIs INTERNAS
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'verified'])->prefix('api')->name('api.')->group(function () {
    Route::get('/specialties/{specialty}/exam-types', function (App\Models\Specialty $specialty) {
        return $specialty->examTypes()->where('is_active', true)->get(['id', 'name', 'base_price']);
    })->name('exams.by.specialty');
});

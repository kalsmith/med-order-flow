<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

// Modelos
use App\Models\Specialty;
use App\Models\ExamType;

// Controladores
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\ExamTypeController;
use App\Http\Controllers\MedicalOrderController;
use App\Http\Controllers\PublicOrderController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\CheckoutController;


/*
|--------------------------------------------------------------------------
| 1. RUTAS PÚBLICAS (Landing Page)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    // Tomamos los que tienen hijos (Packs) y los que no (Individuales)
    $packs = ExamType::has('children')->where('is_active', true)->get();
    $individuales = ExamType::doesntHave('children')->where('is_active', true)->take(6)->get();
    return view('welcome', compact('packs', 'individuales'));
})->name('home');

/*
|--------------------------------------------------------------------------
| 2. AUTENTICACIÓN GOOGLE (Socialite) - "EL FAST TRACK"
|--------------------------------------------------------------------------
*/

// IMPORTANTE: Al llamar a esta ruta 'login', Laravel enviará aquí a
// cualquier usuario que intente comprar sin haber iniciado sesión.
// 1. Pon esto después de cualquier Auth::routes() o rutas de Jetstream
Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

// Logout manual
Route::post('/logout', function() {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');


/*
|--------------------------------------------------------------------------
| 3. RUTAS PROTEGIDAS (PACIENTES)
|--------------------------------------------------------------------------
*/
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {

    // --- GRUPO A: ONBOARDING ---
    Route::get('/completar-perfil', [PublicOrderController::class, 'completeProfileForm'])->name('profile.complete');
    Route::post('/completar-perfil', [PublicOrderController::class, 'storeProfile'])->name('profile.store');

    // --- GRUPO B: FLUJO DE COMPRA Y ÓRDENES (Requieren Perfil Completo) ---
    Route::middleware(['check.profile'])->group(function () {

        // 1. Selección y Confirmación (Vista previa antes de pagar)
        // Eliminamos el parámetro ID de la ruta para manejarlo por POST o sesión si prefieres,
        // pero lo mantengo como slug/type para que sea bookmarkable.
        Route::get('/confirmar-pedido/{exam_type}', [PublicOrderController::class, 'confirmOrder'])->name('orders.confirm');

        // 2. Proceso de Checkout (Crear orden + Redirigir a Flow)
        // Esta ruta hace lo que hacía tu 'checkout.process' anterior.
        Route::post('/enviar-pedido', [PublicOrderController::class, 'store'])->name('orders.store.public');

        // 3. Reintento de pago (Para órdenes ya creadas que quedaron 'pending')
        Route::post('/pagar-orden/{order}', [CheckoutController::class, 'repayOrder'])->name('orders.repay');

        // 4. Retorno de Usuario desde la Pasarela (Success/Pending/Cancel)
        // Similar a tu '/payment/flow/return' anterior.
        Route::match(['get', 'post'], '/payment/flow/return', [CheckoutController::class, 'flowReturn'])
            ->name('flow.return');

        // Dashboard y Mis Órdenes
        Route::get('/mis-ordenes', [PublicOrderController::class, 'index'])->name('patient.orders');

        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard.jetstream');
    });
});

/*
|--------------------------------------------------------------------------
| 5. PASARELAS DE PAGO (WEBHOOKS / SERVER-TO-SERVER)
|--------------------------------------------------------------------------
*/
// IMPORTANTE: Estas rutas deben estar fuera de 'auth' y de 'VerifyCsrfToken'
Route::prefix('payment/flow')->group(function () {
    // Confirmación técnica de Flow (Server-to-Server)
    Route::post('/payment/flow/confirmation', [CheckoutController::class, 'handleWebhook'])
    ->name('flow.webhook');
});






/*
|--------------------------------------------------------------------------
| 4. PANEL DE ADMINISTRACIÓN & GESTIÓN CLÍNICA (STAFF)
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard base del Staff
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- MÓDULO: CONFIGURACIÓN (Admin & Director Técnico) ---
    Route::middleware(['role:admin|director_tecnico'])->group(function () {
        Route::resource('specialties', SpecialtyController::class);
        Route::resource('doctors', DoctorController::class);
        Route::resource('exam-types', ExamTypeController::class);
        Route::resource('orders', MedicalOrderController::class)->except(['create', 'store']);
    });

    // --- MÓDULO: OPERACIONES CLÍNICAS (Médicos) ---
    Route::middleware(['role:doctor'])->group(function () {
        Route::get('/clinical-panel', [MedicalOrderController::class, 'index'])->name('doctor.panel');

        // Firma de órdenes
        Route::get('/orders/{order}/sign', [MedicalOrderController::class, 'showSignForm'])->name('orders.sign.form');
        Route::post('/orders/{order}/sign', [MedicalOrderController::class, 'processSignature'])->name('orders.sign.process');
    });

    // --- MÓDULO: FINANZAS (Contables) ---
    Route::middleware(['role:contable|admin'])->group(function () {
        Route::get('/reports', [DashboardController::class, 'reports'])->name('reports');
        Route::get('/accounting', [DashboardController::class, 'reports'])->name('accounting.index');
    });
});

/*
|--------------------------------------------------------------------------
| 5. UTILIDADES & APIs
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'verified'])->prefix('api')->name('api.')->group(function () {
    Route::get('/specialties/{specialty}/exam-types', function (Specialty $specialty) {
        return $specialty->examTypes()->where('is_active', true)->get(['id', 'name', 'base_price']);
    })->name('exams.by.specialty');
});

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
use App\Http\Controllers\FlowController;

/*
|--------------------------------------------------------------------------
| 1. RUTAS PÚBLICAS (Landing Page)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    $packs = ExamType::has('children')->where('is_active', true)->get();
    $individuales = ExamType::doesntHave('children')->where('is_active', true)->take(6)->get();
    return view('welcome', compact('packs', 'individuales'));
})->name('home');


/*
|--------------------------------------------------------------------------
| 2. AUTENTICACIÓN GOOGLE (Socialite)
|--------------------------------------------------------------------------
*/
// Esta línea es el "puente": si el middleware 'auth' rebota a alguien, lo manda a Google.
// No interfiere con tu login interno si el staff entra directo a su ruta de admin.
//Route::get('/login', [GoogleController::class, 'redirectToGoogle'])->name('login');

Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

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

    /** * FLUJO PMV: Estas rutas permiten configurar la orden y familiares.
     * Se sacan de 'check.profile' para evitar el rebote al login si el perfil está incompleto.
     */
    Route::get('/orden-personalizada', [PublicOrderController::class, 'customOrder'])->name('orders.custom');
    Route::get('/confirmar-orden-especial', [PublicOrderController::class, 'confirmCustomOrder'])->name('orders.custom.confirm');
    Route::get('/confirmar-pedido/{exam_type}', [PublicOrderController::class, 'confirmOrder'])->name('orders.confirm');

    // Gestión de Perfil
    Route::get('/completar-perfil', [PublicOrderController::class, 'completeProfileForm'])->name('profile.complete');
    Route::post('/completar-perfil', [PublicOrderController::class, 'storeProfile'])->name('profile.store');

    // Rutas que SI requieren que el perfil (RUT, etc.) esté validado para finalizar la compra o ver documentos
    Route::middleware(['check.profile'])->group(function () {

        Route::post('/enviar-pedido', [PublicOrderController::class, 'store'])->name('orders.store.public');

        // Checkout de Pago
        Route::get('/checkout/{order}', [CheckoutController::class, 'index'])->name('checkout.index');
        Route::post('/checkout/{order}/process', [CheckoutController::class, 'process'])->name('checkout.process');

        Route::get('/mis-ordenes', [PublicOrderController::class, 'index'])->name('patient.orders');
        Route::get('/dashboard', function () { return view('dashboard'); })->name('dashboard.jetstream');

        Route::get('/orders/download/{order}', [PublicOrderController::class, 'download'])->name('orders.download');
        Route::post('/mis-ordenes/{order}/retry-payment', [PublicOrderController::class, 'retryPayment'])->name('orders.retryPayment');
    });
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

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware(['role:admin|director_tecnico'])->group(function () {
        Route::resource('specialties', SpecialtyController::class);
        Route::resource('doctors', DoctorController::class);
        Route::resource('exam-types', ExamTypeController::class);
        Route::resource('orders', MedicalOrderController::class)->except(['create', 'store']);
    });

    Route::middleware(['role:doctor'])->group(function () {
        Route::get('/clinical-panel', [MedicalOrderController::class, 'index'])->name('doctor.panel');
        Route::get('/orders/{order}/sign', [MedicalOrderController::class, 'showSignForm'])->name('orders.sign.form');
        Route::post('/orders/{order}/sign', [MedicalOrderController::class, 'processSignature'])->name('orders.sign.process');
    });

    Route::middleware(['role:contable|admin'])->group(function () {
        Route::get('/reports', [DashboardController::class, 'reports'])->name('reports');
        Route::get('/accounting', [DashboardController::class, 'reports'])->name('accounting.index');
    });
});

/*
|--------------------------------------------------------------------------
| 5. PASARELAS DE PAGO (Flow)
|--------------------------------------------------------------------------
*/
Route::prefix('payment/flow')->group(function () {
    Route::match(['get', 'post'], '/return', [FlowController::class, 'returnUrl'])->name('flow.return');
    Route::post('/confirmation', [FlowController::class, 'confirmation'])->name('flow.webhook');
    Route::post('/refund-confirmation', [FlowController::class, 'refundConfirmation'])->name('flow.refund.webhook');
    Route::get('/cancel', [FlowController::class, 'cancel'])->name('flow.cancel');
    Route::get('/fail', [FlowController::class, 'fail'])->name('flow.fail');
});

// Éxito de pago (Pública con seguridad interna en el controlador)
Route::get('/pago-exitoso/{order?}', [PublicOrderController::class, 'showSuccess'])->name('payment.success');

/*
|--------------------------------------------------------------------------
| 6. UTILIDADES & APIs
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'verified'])->prefix('api')->name('api.')->group(function () {
    Route::get('/specialties/{specialty}/exam-types', function (Specialty $specialty) {
        return $specialty->examTypes()->where('is_active', true)->get(['id', 'name', 'base_price']);
    })->name('exams.by.specialty');
});

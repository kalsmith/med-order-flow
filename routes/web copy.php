<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Modelos
use App\Models\Specialty;
use App\Models\ExamType;

// Controladores Staff
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\ExamTypeController;
use App\Http\Controllers\MedicalOrderController;

// Controladores Pacientes / Público
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\PublicOrderController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FlowController;

/*
|--------------------------------------------------------------------------
| 1. RUTAS PÚBLICAS & ACCESO
|--------------------------------------------------------------------------
*/

// Bloqueo de la ruta por defecto de Jetstream/Fortify
Route::get('/login', function () {
    abort(404);
});

// La raíz vuelve a estar habilitada para pacientes (Landing Page)
Route::get('/', function () {
    $packs = ExamType::has('children')->where('is_active', true)->get();
    $individuales = ExamType::doesntHave('children')->where('is_active', true)->take(6)->get();
    return view('welcome', compact('packs', 'individuales'));
})->name('home');

// Acceso oficial para Staff (Email/Password)
// Mostramos el formulario
Route::get('/acceso', function() {
    return view('auth.login');
})->name('login');

// Procesamos el inicio de sesión (Permite que el POST funcione en /acceso)
Route::post('/acceso', [\Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::class, 'store']);

// Acceso para Pacientes (Google)
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
| 2. DISTRIBUIDOR DE TRÁFICO (Redirección Post-Login)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'verified'])->get('/home', function () {
    $user = Auth::user();

    // Prioridad Staff: Van al panel de gestión
    if ($user->hasAnyRole(['admin', 'doctor', 'director_tecnico', 'contable'])) {
        return redirect()->route('admin.panel');
    }

    // Pacientes: Si no tienen perfil (RUT), a completar perfil
    if (!$user->patients()->where('relationship', 'self')->exists()) {
        return redirect()->route('profile.complete');
    }

    // Pacientes con perfil: A sus órdenes
    return redirect()->route('patient.orders');
})->name('user.dispatch');

/*
|--------------------------------------------------------------------------
| 3. PANEL DE GESTIÓN (STAFF / MUNDO CLÍNICO)
|--------------------------------------------------------------------------
*/
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'role:admin|doctor|director_tecnico|contable'
])->prefix('gestion')->name('admin.')->group(function () {

    // Panel Principal
    Route::get('/panel', [DashboardController::class, 'index'])->name('panel');

    // Módulo Doctores
    Route::middleware(['role:doctor'])->group(function () {
        Route::get('/clinico', [MedicalOrderController::class, 'index'])->name('doctor.panel');
        Route::get('/ordenes/{order}/revisar', [MedicalOrderController::class, 'showSignForm'])->name('orders.sign.form');
        Route::post('/ordenes/{order}/firmar', [MedicalOrderController::class, 'processSignature'])->name('orders.sign.process');
        Route::post('/ordenes/{order}/rechazar', [MedicalOrderController::class, 'rejectCustomOrder'])->name('orders.reject');
    });

    // Administración y QF (Director Técnico)
    Route::middleware(['role:admin|director_tecnico'])->group(function () {
        Route::resource('especialidades', SpecialtyController::class)
            ->names('specialties')
            ->parameters(['especialidades' => 'specialty']);

        Route::resource('medicos', DoctorController::class)
            ->names('admin.doctors')
            ->parameters(['medicos' => 'doctor']);

        Route::resource('examenes', ExamTypeController::class)
            ->names('exam-types')
            ->parameters(['examenes' => 'exam_type']);

        Route::resource('ordenes', MedicalOrderController::class)->names('orders')->except(['create', 'store']);
    });

    // Módulo Contable
    Route::middleware(['role:contable|admin'])->group(function () {
        Route::get('/reportes', [DashboardController::class, 'reports'])->name('reports');
        Route::get('/contabilidad', [DashboardController::class, 'reports'])->name('accounting.index');
    });
});






/*
|--------------------------------------------------------------------------
| 4. PORTAL DE PACIENTES (CLIENTES)
|--------------------------------------------------------------------------
*/
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {

    // Flujo de compra inicial
    Route::get('/orden-personalizada', [PublicOrderController::class, 'customOrder'])->name('orders.custom');
    Route::get('/confirmar-orden-especial', [PublicOrderController::class, 'confirmCustomOrder'])->name('orders.custom.confirm');
    Route::get('/confirmar-pedido/{exam_type}', [PublicOrderController::class, 'confirmOrder'])->name('orders.confirm');

    // Gestión de Perfil Legal (RUT)
    Route::get('/completar-perfil', [PublicOrderController::class, 'completeProfileForm'])->name('profile.complete');
    Route::post('/completar-perfil', [PublicOrderController::class, 'storeProfile'])->name('profile.store');

    // Acciones con Perfil Validado
    Route::middleware(['check.profile'])->group(function () {
        Route::get('/mis-ordenes', [PublicOrderController::class, 'index'])->name('patient.orders');
        Route::post('/enviar-pedido', [PublicOrderController::class, 'store'])->name('orders.store.public');
        Route::get('/descargar/{order}', [PublicOrderController::class, 'download'])->name('orders.download');

        // Checkout & Pagos
        Route::get('/checkout/{order}', [CheckoutController::class, 'index'])->name('checkout.index');
        Route::post('/checkout/{order}/process', [CheckoutController::class, 'process'])->name('checkout.process');
        Route::post('/mis-ordenes/{order}/reintentar-pago', [PublicOrderController::class, 'retryPayment'])->name('orders.retryPayment');
    });
});

/*
|--------------------------------------------------------------------------
| 5. PAGOS & WEBHOOKS (FLOW)
|--------------------------------------------------------------------------
*/
Route::prefix('payment/flow')->group(function () {
    Route::match(['get', 'post'], '/return', [FlowController::class, 'returnUrl'])->name('flow.return');
    Route::post('/confirmation', [FlowController::class, 'confirmation'])->name('flow.webhook');
    Route::post('/refund-confirmation', [FlowController::class, 'refundConfirmation'])->name('flow.refund.webhook');
    Route::get('/cancel', [FlowController::class, 'cancel'])->name('flow.cancel');
    Route::get('/fail', [FlowController::class, 'fail'])->name('flow.fail');
});

Route::get('/pago-exitoso/{order?}', [PublicOrderController::class, 'showSuccess'])->name('payment.success');

/*
|--------------------------------------------------------------------------
| 6. APIs INTERNAS
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'verified'])->prefix('api')->name('api.')->group(function () {
    Route::get('/specialties/{specialty}/exam-types', function (Specialty $specialty) {
        return $specialty->examTypes()->where('is_active', true)->get(['id', 'name', 'base_price']);
    })->name('exams.by.specialty');
});

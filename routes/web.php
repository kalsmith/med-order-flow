<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Modelos
use App\Models\Specialty;
use App\Models\Faq;

// Controladores
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\PayoutController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\ExamTypeController;
use App\Http\Controllers\MedicalOrderController;
use App\Http\Controllers\Public\LandingController;
use App\Http\Controllers\Patient\OrderFlowController;
use App\Http\Controllers\Patient\PatientOrderController;
use App\Http\Controllers\Patient\PatientCircleController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FlowController;
use App\Http\Controllers\OrderValidationController;
use App\Http\Controllers\ProfileController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

/*
|--------------------------------------------------------------------------
| 1. RUTAS PÚBLICAS
|--------------------------------------------------------------------------
*/
Route::get('/', [LandingController::class, 'index'])->name('home');

// Validación de orden por QR/Link
Route::get('/v/{id}', [OrderValidationController::class, 'show'])->name('validate.order');

// Contenido Legal (Slug)
Route::get('/legal/{slug}', function ($slug) {
    $faq = Faq::where('slug', $slug)->where('is_active', true)->firstOrFail();
    return view('public.legal', compact('faq'));
})->name('legal.show');

/*
|--------------------------------------------------------------------------
| 2. AUTENTICACIÓN (Login & Google)
|--------------------------------------------------------------------------
*/
Route::get('/login', function () { abort(404); });
Route::get('/acceso', function() { return view('auth.login'); })->name('login');
Route::post('/acceso', [AuthenticatedSessionController::class, 'store']);

// Google Auth (Libre de middlewares de rol para evitar bloqueos)
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
| 3. DISTRIBUIDOR DE TRÁFICO (Post-Login)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'verified'])->get('/home', function () {
    $user = Auth::user();
    if ($user->hasAnyRole(['admin', 'doctor', 'director_tecnico', 'contable'])) {
        return redirect()->route('admin.panel');
    }
    // Si no tiene perfil creado, lo dejamos fluir o lo mandamos a completar
    if (!$user->patients()->where('relationship', 'self')->exists()) {
        return redirect()->intended(route('home'));
    }
    return redirect()->route('patient.orders');
})->name('user.dispatch');

/*
|--------------------------------------------------------------------------
| 4. GESTIÓN DE CUENTA (Común para todos los usuarios autenticados)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    // Estas rutas solo requieren estar logueado, no un rol específico
    Route::get('/cuenta/configuracion', [ProfileController::class, 'showDeletePage'])->name('profile.delete.view');
    Route::post('/cuenta/solicitar-borrado', [ProfileController::class, 'requestAccountDeletion'])->name('profile.delete.request');
    Route::post('/cuenta/confirmar-borrado', [ProfileController::class, 'confirmAccountDeletion'])->name('profile.delete.confirm');
});

/*
|--------------------------------------------------------------------------
| 5. PANEL DE GESTIÓN (STAFF: Admin, Doctor, etc.)
|--------------------------------------------------------------------------
*/
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'role:admin|doctor|director_tecnico|contable'
])->prefix('gestion')->name('admin.')->group(function () {

    Route::get('/panel', [DashboardController::class, 'index'])->name('panel');
    Route::get('/payouts/{payout}/comprobante', [PayoutController::class, 'downloadEvidence'])->name('payouts.download');

    // RUTAS DOCTOR
    Route::middleware(['role:doctor'])->group(function () {
        Route::get('/clinico', [MedicalOrderController::class, 'index'])->name('doctor.panel');
        Route::get('/mi-billetera', [PayoutController::class, 'doctorWallet'])->name('payouts.wallet');
        Route::post('/retiros/solicitar', [PayoutController::class, 'requestStore'])->name('payouts.request');

        Route::prefix('ordenes-clinicas')->name('orders.')->group(function () {
            Route::get('/{order}/revisar', [MedicalOrderController::class, 'showSignForm'])->name('sign.form');
            Route::post('/{order}/firmar', [MedicalOrderController::class, 'processSignature'])->name('sign.process');
            Route::post('/{order}/rechazar', [MedicalOrderController::class, 'rejectOrder'])->name('reject');
            Route::post('/{order}/liberar', [MedicalOrderController::class, 'releaseOrder'])->name('release');
            Route::post('/{order}/derivar', [MedicalOrderController::class, 'derivateOrder'])->name('derivate');
            Route::post('/{order}/anular-firma', [MedicalOrderController::class, 'voidSignature'])->name('void');
            Route::get('/{order}/pdf', [MedicalOrderController::class, 'generatePdf'])->name('pdf');
        });
    });

    // RUTAS ADMINISTRACIÓN
    Route::middleware(['role:admin|director_tecnico'])->group(function () {
        Route::resource('especialidades', SpecialtyController::class)->names('specialties');
        Route::resource('medicos', DoctorController::class)->names('doctors');
        Route::resource('examenes', ExamTypeController::class)->names('exam-types')->parameters(['examenes' => 'exam_type']);
        Route::resource('preguntas-frecuentes', FaqController::class)->names('faqs')->parameters(['preguntas-frecuentes' => 'faq']);
        Route::resource('ordenes', MedicalOrderController::class)->names('orders')->parameters(['ordenes' => 'order'])->except(['create', 'store']);
    });

    // RUTAS CONTABILIDAD
    Route::middleware(['role:contable|admin'])->group(function () {
        Route::get('/reportes', [DashboardController::class, 'businessReports'])->name('reports');
        Route::get('/contabilidad', [DashboardController::class, 'reports'])->name('accounting.index');
        Route::get('/pagos-medicos', [PayoutController::class, 'index'])->name('payouts.index');
        Route::post('/pagos-medicos/{payout}/procesar', [PayoutController::class, 'process'])->name('payouts.process');
    });
});

/*
|--------------------------------------------------------------------------
| 6. PORTAL DE PACIENTES
|--------------------------------------------------------------------------
*/
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'role:paciente'
])->group(function () {

    // Perfil y Familia
    Route::get('/completar-perfil-obligatorio', [OrderFlowController::class, 'handle'])
        ->defaults('type', 'personalizada')->name('profile.complete');

    Route::get('/mi-circulo', [PatientCircleController::class, 'index'])->name('patient.circle');
    Route::post('/mi-circulo/agregar', [PatientCircleController::class, 'store'])->name('patient.circle.store');
    Route::delete('/mi-circulo/{patient}', [PatientCircleController::class, 'destroy'])->name('patient.circle.destroy');

    // Embudo de Compra
    Route::get('/solicitar/{type}/{id?}', [OrderFlowController::class, 'handle'])->name('order.flow');
    Route::post('/validar-perfil-flow', [OrderFlowController::class, 'storeProfile'])->name('profile.store.flow');

    // Acciones con Perfil Completo
    Route::middleware(['check.profile'])->group(function () {
        Route::get('/mis-ordenes', [PatientOrderController::class, 'index'])->name('patient.orders');
        Route::post('/enviar-pedido', [PatientOrderController::class, 'store'])->name('orders.store.public');
        Route::get('/descargar/{order}', [PatientOrderController::class, 'download'])->name('orders.download');

        // Pago
        Route::get('/checkout/{order}/process', [CheckoutController::class, 'process'])->name('checkout.index');
        Route::get('/checkout/{order}/pay', [CheckoutController::class, 'process'])
            ->name('checkout.process')
            ->where('order', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');

        Route::get('/pago-exitoso/{order?}', [PatientOrderController::class, 'showSuccess'])->name('payment.success');
    });
});

/*
|--------------------------------------------------------------------------
| 7. PASARELA DE PAGO (FLOW)
|--------------------------------------------------------------------------
*/
Route::prefix('payment/flow')->group(function () {
    Route::match(['get', 'post'], '/return', [FlowController::class, 'returnUrl'])->name('flow.return');
    Route::post('/confirmation', [FlowController::class, 'confirmation'])->name('flow.webhook');
    Route::post('/refund-confirmation', [FlowController::class, 'refundConfirmation'])->name('flow.refund.webhook');
    Route::get('/status/{token}', [FlowController::class, 'viewStatus'])->name('payment.status');
    Route::get('/cancel', [FlowController::class, 'cancel'])->name('flow.cancel');
    Route::get('/fail', [FlowController::class, 'fail'])->name('flow.fail');
});

/*
|--------------------------------------------------------------------------
| 8. APIs INTERNAS
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'verified'])->prefix('api')->name('api.')->group(function () {
    Route::get('/specialties/{specialty}/exam-types', function (Specialty $specialty) {
        return $specialty->examTypes()->where('is_active', true)->get(['id', 'name', 'base_price']);
    })->name('exams.by.specialty');
});

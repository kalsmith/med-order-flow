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
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\Public\LandingController;
use App\Http\Controllers\Patient\OrderFlowController;
use App\Http\Controllers\Patient\PatientOrderController;
use App\Http\Controllers\Patient\PatientCircleController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FlowController;
use App\Http\Controllers\OrderValidationController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

/*
|--------------------------------------------------------------------------
| 1. RUTAS PÚBLICAS & ACCESO
|--------------------------------------------------------------------------
*/
Route::get('/', [LandingController::class, 'index'])->name('home');

// --- Rutas Públicas del Blog ---
Route::group(['prefix' => 'blog'], function () {

    // Listado principal: med-order-flow.soltys.cl/blog
    Route::get('/', [PostController::class, 'publicIndex'])
        ->name('blog.index');

    // Detalle del post: med-order-flow.soltys.cl/blog/titulo-del-articulo
    // Usamos {slug} para que la URL sea legible por humanos y buscadores
    Route::get('/{slug}', [PostController::class, 'publicShow'])
        ->name('blog.show');
});

// --- ACCESO A FIRMAS ---
Route::get('/view-signature/{filename}', [DoctorController::class, 'showSignature'])->name('public.signature.show');

// Validación pública de órdenes
Route::get('/v/{id}', [OrderValidationController::class, 'show'])->name('validate.order');

// Rutas Legales
Route::get('/legal/{slug}', function ($slug) {
    $faq = Faq::where('slug', $slug)->where('is_active', true)->firstOrFail();
    return view('public.legal', compact('faq'));
})->name('legal.show');

// Auth Tradicional & Google
Route::get('/login', function () { abort(404); });
Route::get('/acceso', function() { return view('auth.login'); })->name('login');
Route::post('/acceso', [AuthenticatedSessionController::class, 'store']);

Route::get('/auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

Route::post('/logout', function() {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');


Route::get('robots.txt', function () {
    return response(view('public.robots'), 200)
        ->header('Content-Type', 'text/plain');
});

Route::get('sitemap.xml', [LandingController::class, 'sitemap']);
/*
|--------------------------------------------------------------------------
| 2. DISTRIBUIDOR DE TRÁFICO (Post-Login)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->get('/home', function () {
    $user = Auth::user();
    if ($user->hasAnyRole(['admin', 'doctor', 'director_tecnico', 'contable'])) {
        return redirect()->route('admin.panel');
    }
    // Si no tiene perfil de paciente creado
    if (!$user->patients()->where('relationship', 'self')->exists()) {
        return redirect()->intended(route('home'));
    }
    return redirect()->route('patient.orders');
})->name('user.dispatch');


/*
|--------------------------------------------------------------------------
| 3. PANEL DE GESTIÓN (STAFF)
|--------------------------------------------------------------------------
*/
Route::middleware([
    'auth',
    config('jetstream.auth_session'),
    'verified',
    'role:admin|doctor|director_tecnico|contable'
])->prefix('gestion')->name('admin.')->group(function () {


    // --- ASÍ DEBEN QUEDAR ---
    Route::get('/mi-perfil', [ProfileController::class, 'show'])->name('profile.show');
    // El POST se queda para procesar el formulario que estará dentro de 'show'
    Route::post('/mi-perfil/password', [ProfileController::class, 'updatePassword'])->name('password.update');

    Route::get('/panel', [DashboardController::class, 'index'])->name('panel');
    Route::get('/payouts/{payout}/comprobante', [PayoutController::class, 'downloadEvidence'])->name('payouts.download');

    // --- ACCIONES CLÍNICAS COMPARTIDAS (Médico & Director Técnico) ---
    // Se extrae la generación de PDF para que ambos roles puedan auditar/visualizar
    Route::middleware(['role:doctor|director_tecnico'])->group(function () {
        Route::get('/ordenes-clinicas/{order}/pdf', [MedicalOrderController::class, 'generatePdf'])->name('orders.pdf');
    });


    // --- GESTIÓN DE CONTENIDO & MARKETING (NUEVO) ---
    // Admin, Doctor y DT pueden escribir en el blog y gestionar los packs
    Route::middleware(['role:admin|doctor|director_tecnico'])->group(function () {
        Route::resource('blog', PostController::class)
            ->names('posts')
            ->parameters(['blog' => 'post']);

        // Route::resource('packs-examenes', PackController::class)
        //     ->names('packs')
        //     ->parameters(['packs-examenes' => 'pack']);
    });



    // --- RUTAS EXCLUSIVAS DEL DOCTOR ---
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
            Route::get('/doctor/wallet/export', [PayoutController::class, 'exportWallet'])->name('doctor.wallet.export');
            // La ruta PDF se movió al bloque compartido superior
        });
    });

    // --- RUTAS EXCLUSIVAS DEL DIRECTOR TÉCNICO (Supervisión Clínica) ---
    Route::middleware(['role:director_tecnico'])->group(function () {
        // Vista de órdenes dedicada para el DT
        Route::get('/ordenes', [MedicalOrderController::class, 'clinicalIndex'])->name('orders.index');

        // Reporte de calidad clínica
        Route::get('/reportes-calidad-clinica', [DashboardController::class, 'clinicalQualityReports'])->name('reports.clinical');

        Route::resource('ordenes', MedicalOrderController::class)
            ->names('orders')
            ->parameters(['ordenes' => 'order'])
            ->except(['index', 'create', 'store']);
    });




    // --- RUTAS COMPARTIDAS (ADMIN & DIRECTOR TÉCNICO) ---
    Route::middleware(['role:admin|director_tecnico'])->group(function () {
        Route::resource('especialidades', SpecialtyController::class)
        ->names('specialties')
        ->parameters(['especialidades' => 'specialty']);
        Route::resource('medicos', DoctorController::class)->names('doctors');
        Route::get('/signatures/{filename}', [DoctorController::class, 'showSignature'])->name('signatures.show');
        Route::resource('examenes', ExamTypeController::class)->names('exam-types')->parameters(['examenes' => 'exam_type']);
    });

    // --- RUTAS EXCLUSIVAS DE ADMINISTRACIÓN (FAQ / Contenidos) ---
    Route::middleware(['role:admin'])->group(function () {

        Route::resource('preguntas-frecuentes', FaqController::class)->names('faqs')->parameters(['preguntas-frecuentes' => 'faq']);

        Route::resource('usuarios', UserController::class)
            ->names('users')
            ->parameters(['usuarios' => 'user']); // <--- ESTO ES LA CLAVE
    });

    // --- RUTAS DE FINANZAS (ADMIN & CONTABLE) ---
    Route::middleware(['role:contable|admin'])->group(function () {
        Route::get('/reportes', [DashboardController::class, 'businessReports'])->name('reports');
        Route::get('/contabilidad', [DashboardController::class, 'reports'])->name('accounting.index');
        Route::get('/pagos-medicos', [PayoutController::class, 'index'])->name('payouts.index');
        Route::post('/pagos-medicos/{payout}/procesar', [PayoutController::class, 'process'])->name('payouts.process');
    });
});



/*
|--------------------------------------------------------------------------
| 4. PORTAL DE PACIENTES & PAGOS
|--------------------------------------------------------------------------
*/


Route::middleware([
    'auth',
    config('jetstream.auth_session'),
    'verified',
    'role:paciente'
])->group(function () {

    // Gestión de Perfil
    Route::get('/perfil/eliminar', [ProfileController::class, 'showDeletePage'])->name('profile.delete.view');
    Route::post('/perfil/eliminar/solicitar', [ProfileController::class, 'requestAccountDeletion'])->name('profile.delete.request');
    Route::post('/perfil/eliminar/confirmar', [ProfileController::class, 'confirmAccountDeletion'])->name('profile.delete.execute');

    Route::get('/completar-perfil-obligatorio', [OrderFlowController::class, 'handle'])
        ->defaults('type', 'personalizada')
        ->name('profile.complete');

    // Círculo Familiar
    Route::get('/mi-circulo', [PatientCircleController::class, 'index'])->name('patient.circle');
    Route::post('/mi-circulo/agregar', [PatientCircleController::class, 'store'])->name('patient.circle.store');
    Route::delete('/mi-circulo/{patient}', [PatientCircleController::class, 'destroy'])->name('patient.circle.destroy');


    Route::get('/mi-historial-examenes', [PatientCircleController::class, 'examHistory'])->name('patient.exam.history');

    // Flujo de Orden
    Route::get('/solicitar/{type}/{id?}', [OrderFlowController::class, 'handle'])->name('order.flow');
    Route::post('/validar-perfil-flow', [OrderFlowController::class, 'storeProfile'])->name('profile.store.flow');

    // Rutas protegidas por Perfil Completo
    Route::middleware(['check.profile'])->group(function () {
        Route::get('/mis-ordenes', [PatientOrderController::class, 'index'])->name('patient.orders');
        Route::post('/enviar-pedido', [PatientOrderController::class, 'store'])->name('orders.store.public');
        Route::get('/descargar/{order}', [PatientOrderController::class, 'download'])->name('orders.download');


        // Checkout
        Route::get('/checkout/{order}/process', [CheckoutController::class, 'process'])->name('checkout.index');
        Route::get('/checkout/{order}/pay', [CheckoutController::class, 'process'])
            ->name('checkout.process')
            ->where('order', '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}');

        Route::get('/pago-exitoso/{order?}', [PatientOrderController::class, 'showSuccess'])->name('payment.success');
    });
});

/*
|--------------------------------------------------------------------------
| 5. WEBHOOKS & ESTADO DE FLOW
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
| 6. APIs INTERNAS
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->prefix('api')->name('api.')->group(function () {
    Route::get('/specialties/{specialty}/exam-types', function (Specialty $specialty) {
        return $specialty->examTypes()->where('is_active', true)->get(['id', 'name', 'base_price']);
    })->name('exams.by.specialty');
});

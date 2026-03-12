<?php

namespace App\Http\Controllers;

use App\Models\MedicalOrder;
use App\Models\ExamType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicalOrderController extends Controller
{
    /**
     * Listado de órdenes: Inteligente según el Rol.
     */
public function index()
{
    $user = Auth::user();
    // Importante: Usamos withTrashed() en la relación patient para que no explote si el familiar fue borrado
    $query = MedicalOrder::with(['patient' => function($q) {
        $q->withTrashed();
    }, 'doctor.user', 'examType']);

    if ($user->hasRole('doctor')) {
        $doctor = $user->doctor;

        $query->where(function($q) use ($doctor) {
            // 1. Ver lo que YA es mío (sin importar el estado o si tiene examen tipo)
            $q->where('doctor_id', $doctor->id)
            // 2. O ver lo que está pendiente y es de mi especialidad
            ->orWhere(function($sq) use ($doctor) {
                $sq->whereNull('doctor_id')
                   ->where('status', 'pending')
                   ->whereHas('examType', function($eq) use ($doctor) {
                       $eq->where('specialty_id', $doctor->specialty_id);
                   });
            })
            // 3. O ver solicitudes especiales (SIN exam_type_id) que estén pendientes
            // (Asumiendo que los doctores de Medicina General o Staff pueden verlas)
            ->orWhere(function($sq) {
                $sq->whereNull('doctor_id')
                   ->whereNull('exam_type_id')
                   ->where('status', 'pending');
            });
        });
    }

    $orders = $query->latest()->paginate(10);
    return view('admin.orders.index', compact('orders'));
}

    /**
     * Formulario para que un médico emita una orden manualmente.
     */
    public function create()
    {
        $doctor = Auth::user()->doctor;

        if (!$doctor) {
            return redirect()->back()->with('error', 'No tienes un perfil de doctor asociado.');
        }

        // Exámenes de su especialidad + Medicina General (Sugerencia Senior: usar IDs o Slugs)
        $exams = ExamType::where('specialty_id', $doctor->specialty_id)
            ->orWhereHas('specialty', function($q) {
                $q->where('name', 'LIKE', '%General%');
            })
            ->where('is_active', true)
            ->get();

        return view('admin.orders.create', compact('exams'));
    }

    /**
     * Almacenar la orden generada.
     */
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'exam_type_id' => 'required|exists:exam_types,id',
            'amount' => 'required|numeric|min:0',
        ]);

        $doctor = Auth::user()->doctor;

        $order = MedicalOrder::create([
            'patient_id' => $request->patient_id,
            'doctor_id' => $doctor->id,
            'exam_type_id' => $request->exam_type_id,
            'amount' => $request->amount,
            'status' => 'pending',
            'verification_code' => MedicalOrder::generateUniqueVerificationCode(),
        ]);

        // Redirección corregida con el nombre de ruta actual
        return redirect()->route('admin.orders.index')
                         ->with('success', 'Orden médica generada correctamente.');
    }

    /**
     * Muestra el formulario de firma (Vista previa antes de firmar)
     */
public function showSignForm(MedicalOrder $order)
{
    // Usamos withTrashed para que el doctor vea al paciente aunque esté "soft-deleted"
    $order->load(['patient' => fn($q) => $q->withTrashed(), 'examType']);

    $user = Auth::user();

    // VALIDACIÓN DE PERMISOS CORREGIDA:
    // Si la orden ya tiene doctor y no soy yo -> Bloqueo
    if ($order->doctor_id && $order->doctor_id !== $user->doctor->id) {
        abort(403, 'Esta orden pertenece a otro profesional.');
    }

    // Si la orden ya está firmada, no debería entrar al "Formulario de Firma",
    // pero sí debería poder VERLA. Podrías redirigir a un 'show' o manejarlo en la vista.
    if ($order->status === 'signed') {
        // Opcional: redirect()->route('admin.orders.show', $order);
    }

    $user->doctor->load('specialty');
    return view('admin.orders.sign', compact('order'));
}

    /**
     * Procesa la firma digital
     */
    public function processSignature(Request $request, MedicalOrder $order)
    {
        $doctor = Auth::user()->doctor;

        // Validación de seguridad extra:
        // Si la orden ya tiene doctor y no es el que intenta firmar, denegar.
        if ($order->doctor_id && $order->doctor_id !== $doctor->id) {
            abort(403, 'Esta orden ya fue tomada por otro profesional.');
        }

        $order->update([
            'doctor_id' => $doctor->id, // Importante: Asignamos el ID al momento de firmar
            'status' => 'signed',
            'signed_at' => now(),
        ]);

        // Aquí es donde dispararías el Job para generar el PDF
        // GenerateOrderPdf::dispatch($order);

        return redirect()->route('admin.doctor.panel')
                        ->with('success', 'Orden firmada digitalmente con éxito.');
    }
}

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
    $query = MedicalOrder::with(['patient.user', 'doctor.user', 'examType']);

    if ($user->hasRole('doctor')) {
        $query->where('doctor_id', $user->doctor->id);
        $orders = $query->latest()->paginate(10);

        // Intentará cargar admin/orders/doctor_index.blade.php
        // Si no la has creado, usa 'admin.orders.index' por ahora
        return view('admin.orders.index', compact('orders'));
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
        // Cargamos las relaciones para que la vista tenga todo a mano
        $order->load(['patient.user', 'examType']);

        // Validar dueño
        if (Auth::user()->hasRole('doctor') && $order->doctor_id !== Auth::user()->doctor->id) {
            abort(403, 'No tienes permiso para firmar esta orden.');
        }

        // Cargamos al doctor con su especialidad explícitamente para la firma
        auth()->user()->doctor->load('specialty');

        return view('admin.orders.sign', compact('order'));
    }

    /**
     * Procesa la firma digital
     */
    public function processSignature(Request $request, MedicalOrder $order)
    {
        // Aquí vendrá tu lógica de firma electrónica
        $order->update([
            'status' => 'signed',
            'signed_at' => now(),
        ]);

        return redirect()->route('admin.doctor.panel')
                         ->with('success', 'Orden firmada digitalmente con éxito.');
    }
}

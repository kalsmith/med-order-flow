<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\ExamType;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderFlowController extends Controller
{
public function handle(Request $request, $type, $id = null)
{
    $user = Auth::user();
    $patient = $user->patients()->where('relationship', 'self')->first();

    // Cargamos el examen siempre que haya un ID, para poder mostrar el nombre en cualquier vista
    $exam_type = $id ? ExamType::find($id) : null;

    // CASO A: No tiene perfil.
    if (!$patient) {
        // Pasamos también $exam_type para que no de error si la vista intenta mostrar el nombre
        return view('front.flow.complete-profile', compact('type', 'id', 'exam_type'));
    }

    // CASO B: Ya tiene perfil.
    if ($type === 'pack' && $id) {
        $exam_type = ExamType::findOrFail($id); // Usamos $exam_type
        return view('front.flow.confirm-pack', compact('exam_type', 'patient'));
    }

    // NUEVO: Si es un Examen Individual
    if ($type === 'exam' && $id) {
        $exam_type = ExamType::findOrFail($id);
        // Aquí puedes usar la misma vista de confirmación de pack
        // o una específica llamada 'confirm-exam'
        return view('front.flow.confirm-exam', compact('exam_type', 'patient'));
    }


    if ($type === 'personalizada') {
        return view('front.flow.custom-request', compact('patient'));
    }

    return redirect()->route('home');
}

// app/Http/Controllers/Patient/OrderFlowController.php

public function storeProfile(Request $request)
{
    $request->validate([
        'full_name'       => 'required|string|max:255',
        'rut'             => 'required|string|max:12', // Aquí podrías poner una regla de validación de RUT
        'birth_date'      => 'required|date|before:today',
        'gender_biologic' => 'required|in:Masculino,Femenino',
        'phone'           => 'nullable|string|max:20',
    ]);

    // Creamos el paciente "Self" (El dueño de la cuenta)
    Patient::create([
        'user_id'         => Auth::id(),
        'full_name'       => $request->full_name,
        'rut'             => $request->rut,
        'birth_date'      => $request->birth_date,
        'gender_biologic' => $request->gender_biologic,
        'phone'           => $request->phone,
        'relationship'    => 'self',
        'is_primary'      => true, // Lo marcamos como su perfil principal
    ]);

    // Refrescamos para que el método handle() detecte que ya existe el paciente
    return back()->with('success', 'Perfil creado exitosamente.');
}
}

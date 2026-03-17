<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Doctor;
use App\Models\Specialty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DoctorController extends Controller
{
    protected $routePrefix = 'admin.doctors';

    public function index()
    {
        $doctors = Doctor::with(['user', 'specialties'])->latest()->paginate(10);
        return view('admin.doctors.index', compact('doctors'));
    }

    public function create()
    {
        $specialties = Specialty::orderBy('name')->get();
        return view('admin.doctors.create', compact('specialties'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'prefix'      => 'required|string|in:Dr.,Dra.', // Validación del nuevo selector
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'rut'         => 'required|string|unique:doctors,rut',
            'specialties' => 'required|array|min:1',
            'signature'   => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $user = User::create([
                    'name'     => $request->name,
                    'email'    => $request->email,
                    'password' => Hash::make(str_replace(['.', '-'], '', $request->rut)),
                ]);

                $user->assignRole('doctor');

                $path = $request->hasFile('signature')
                    ? $request->file('signature')->store('signatures', 'public')
                    : null;

                $doctor = Doctor::create([
                    'user_id'        => $user->id,
                    'prefix'         => $request->prefix, // Guardamos Dr. o Dra.
                    'rut'            => $request->rut,
                    'rnpi_number'    => $request->rnpi_number,
                    'signature_path' => $path,
                    'is_active'      => true,
                ]);

                $doctor->specialties()->attach($request->specialties);

                return redirect()->route("{$this->routePrefix}.index")
                    ->with('status', 'Doctor registrado exitosamente.');
            });
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Error: ' . $e->getMessage()]);
        }
    }

    // Cambiamos el TypeHint para que coincida con el parámetro del Resource {medico}
    public function edit(Doctor $medico)
    {
        $doctor = $medico; // Para no romper tu vista actual que usa $doctor
        $specialties = Specialty::orderBy('name')->get();
        return view('admin.doctors.edit', compact('doctor', 'specialties'));
    }

    // Cambiamos el TypeHint aquí también
    // Cambiamos el TypeHint aquí también


    public function update(Request $request, Doctor $medico)
{
    $doctor = $medico;

    $request->validate([
        'prefix'             => 'required|string|in:Dr.,Dra.',
        'name'               => 'required|string|max:255',
        'email'              => ['required', 'email', Rule::unique('users')->ignore($doctor->user_id)],
        'rut'                => ['required', Rule::unique('doctors')->ignore($doctor->id)],
        'specialties'        => 'required|array|min:1',
        'is_active'          => 'required|boolean',
        'signature_cropped'  => 'nullable|string', // El Base64 del recorte
        'signature'          => 'nullable|image|mimes:png,jpg,jpeg|max:2048', // El archivo original
        'password'           => 'nullable|string|min:8|confirmed',
    ]);

    try {
        DB::transaction(function () use ($request, $doctor) {
            // 1. Actualizar datos básicos del usuario
            $userData = [
                'name'  => $request->name,
                'email' => $request->email,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $doctor->user->update($userData);

            // 2. Manejo de la firma digital (Priorizamos la recortada de JS)
            if ($request->filled('signature_cropped')) {
                // Eliminar firma anterior si existe
                if ($doctor->signature_path) {
                    Storage::disk('public')->delete($doctor->signature_path);
                }

                // Procesar el string Base64
                $imageData = $request->input('signature_cropped');
                // Separamos el encabezado del contenido (data:image/png;base64,xxxx)
                if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                    $imageContent = substr($imageData, strpos($imageData, ',') + 1);
                    $extension = strtolower($type[1]); // png, jpg, etc.
                    $imageContent = base64_decode($imageContent);

                    if ($imageContent === false) {
                        throw new \Exception('La decodificación de la imagen falló.');
                    }

                    $fileName = 'signatures/sig_' . uniqid() . '.' . $extension;
                    Storage::disk('public')->put($fileName, $imageContent);

                    $doctor->signature_path = $fileName;
                }
            }
            // Fallback: Si no hay recorte pero sí un archivo directo
            elseif ($request->hasFile('signature')) {
                if ($doctor->signature_path) {
                    Storage::disk('public')->delete($doctor->signature_path);
                }
                $doctor->signature_path = $request->file('signature')->store('signatures', 'public');
            }

            // 3. Actualizar datos específicos del doctor
            $doctor->fill($request->only(['prefix', 'rut', 'rnpi_number', 'is_active']));
            $doctor->save();

            // 4. Sincronizar especialidades
            $doctor->specialties()->sync($request->specialties);
        });

        return redirect()->route("{$this->routePrefix}.index")
            ->with('status', 'Perfil del médico actualizado correctamente.');

    } catch (\Exception $e) {
        return back()->withInput()->withErrors(['error' => 'Error al actualizar: ' . $e->getMessage()]);
    }
}


}

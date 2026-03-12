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
public function update(Request $request, Doctor $medico)
{
    $doctor = $medico;

    $request->validate([
        'name'        => 'required|string|max:255',
        'email'       => ['required', 'email', Rule::unique('users')->ignore($doctor->user_id)],
        'rut'         => ['required', Rule::unique('doctors')->ignore($doctor->id)],
        'specialties' => 'required|array|min:1',
        'is_active'   => 'required|boolean',
        'signature'   => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        // Validamos el password solo si el campo no está vacío
        'password'    => 'nullable|string|min:8|confirmed',
    ]);

    try {
        DB::transaction(function () use ($request, $doctor) {
            // 1. Actualizar datos básicos del usuario
            $userData = [
                'name'  => $request->name,
                'email' => $request->email,
            ];

            // 2. Si se ingresó una contraseña, la hasheamos y la sumamos al update
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $doctor->user->update($userData);

            // 3. Manejo de la firma digital
            if ($request->hasFile('signature')) {
                if ($doctor->signature_path) {
                    Storage::disk('public')->delete($doctor->signature_path);
                }
                // Guardamos el nuevo archivo
                $path = $request->file('signature')->store('signatures', 'public');
                $doctor->signature_path = $path;
            }

            // 4. Actualizar datos específicos del doctor
            $doctor->fill($request->only(['rut', 'rnpi_number', 'is_active']));
            $doctor->save();

            // 5. Sincronizar especialidades
            $doctor->specialties()->sync($request->specialties);
        });

        return redirect()->route("{$this->routePrefix}.index")
            ->with('status', 'Perfil del médico actualizado correctamente.');

    } catch (\Exception $e) {
        return back()->withInput()->withErrors(['error' => 'Error al actualizar: ' . $e->getMessage()]);
    }
}
}

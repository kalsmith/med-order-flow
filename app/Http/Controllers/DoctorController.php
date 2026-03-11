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
    /**
     * Nombre base de las rutas para evitar repeticiones.
     */
    protected $routePrefix = 'admin.admin.doctors';

    public function index()
    {
        // Usamos paginate en lugar de get para mejorar el rendimiento si la lista crece
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
            'rnpi_number' => 'nullable|string|max:50',
            'specialties' => 'required|array|min:1',
            'specialties.*' => 'exists:specialties,id',
            'signature'   => 'nullable|image|mimes:png,jpg,jpeg|max:2048', // Subimos a 2MB
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // 1. Crear Usuario. Password por defecto es el RUT sin puntos ni guion
                $user = User::create([
                    'name'     => $request->name,
                    'email'    => $request->email,
                    'password' => Hash::make(str_replace(['.', '-'], '', $request->rut)),
                ]);

                $user->assignRole('doctor');

                // 2. Procesar Firma
                $path = $request->hasFile('signature')
                    ? $request->file('signature')->store('signatures', 'public')
                    : null;

                // 3. Crear Perfil de Doctor
                $doctor = Doctor::create([
                    'user_id'        => $user->id,
                    'rut'            => $request->rut,
                    'rnpi_number'    => $request->rnpi_number,
                    'address'        => $request->address,
                    'signature_path' => $path,
                    'is_active'      => true,
                ]);

                $doctor->specialties()->attach($request->specialties);

                return redirect()->route("{$this->routePrefix}.index")
                    ->with('status', 'Doctor registrado exitosamente.');
            });

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'No se pudo crear el registro: ' . $e->getMessage()]);
        }
    }

    public function edit(Doctor $doctor)
    {
        $specialties = Specialty::orderBy('name')->get();
        return view('admin.doctors.edit', compact('doctor', 'specialties'));
    }

    public function update(Request $request, Doctor $doctor)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => ['required', 'email', Rule::unique('users')->ignore($doctor->user_id)],
            'rut'         => ['required', Rule::unique('doctors')->ignore($doctor->id)],
            'specialties' => 'required|array|min:1',
            'is_active'   => 'required|boolean',
            'signature'   => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        try {
            DB::beginTransaction();

            // Actualizar Usuario
            $doctor->user->update([
                'name'  => $request->name,
                'email' => $request->email,
            ]);

            // Manejo de Firma (Solo si se sube una nueva)
            if ($request->hasFile('signature')) {
                if ($doctor->signature_path) {
                    Storage::disk('public')->delete($doctor->signature_path);
                }
                $doctor->signature_path = $request->file('signature')->store('signatures', 'public');
            }

            // Actualizar datos del médico
            $doctor->fill($request->only(['rut', 'rnpi_number', 'address', 'is_active']));
            $doctor->save();

            // Sincronizar especialidades
            $doctor->specialties()->sync($request->specialties);

            DB::commit();
            return redirect()->route("{$this->routePrefix}.index")
                ->with('status', 'Perfil del médico actualizado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Error al actualizar: ' . $e->getMessage()]);
        }
    }
}

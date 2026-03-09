<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Doctor;
use App\Models\Specialty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DoctorController extends Controller
{
    public function index()
    {
        $doctors = Doctor::with(['user', 'specialties'])->get();
        return view('admin.doctors.index', compact('doctors'));
    }

    public function create()
    {
        $specialties = Specialty::all();
        return view('admin.doctors.create', compact('specialties'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'rut' => 'required|string|unique:doctors,rut',
            'rnpi_number' => 'nullable|string',
            'specialties' => 'required|array',
            'signature' => 'nullable|image|mimes:png,jpg,jpeg|max:1024',
        ]);

        try {
            DB::beginTransaction();

            // 1. Crear Usuario con rol Doctor
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make(str_replace(['.', '-'], '', $request->rut)),
            ]);
            $user->assignRole('doctor');

            // 2. Manejo de Firma Digital (opcional)
            $path = null;
            if ($request->hasFile('signature')) {
                $path = $request->file('signature')->store('signatures', 'public');
            }

            // 3. Crear Perfil de Doctor
            $doctor = Doctor::create([
                'user_id' => $user->id,
                'rut' => $request->rut,
                'rnpi_number' => $request->rnpi_number,
                'address' => $request->address,
                'signature_path' => $path,
                'is_active' => true,
            ]);

            // 4. Sincronizar Especialidades
            $doctor->specialties()->attach($request->specialties);

            DB::commit();
            return redirect()->route('doctors.index')->with('status', 'Doctor registrado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function edit(Doctor $doctor)
    {
        $specialties = Specialty::all();
        return view('admin.doctors.edit', compact('doctor', 'specialties'));
    }

    public function update(Request $request, Doctor $doctor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $doctor->user_id,
            'rut' => 'required|unique:doctors,rut,' . $doctor->id,
            'specialties' => 'required|array',
        ]);

        try {
            DB::beginTransaction();

            // Actualizar Usuario
            $doctor->user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            // Actualizar Firma si viene nueva
            if ($request->hasFile('signature')) {
                if ($doctor->signature_path) Storage::disk('public')->delete($doctor->signature_path);
                $doctor->signature_path = $request->file('signature')->store('signatures', 'public');
            }

            // Actualizar Doctor
            $doctor->update($request->only(['rut', 'rnpi_number', 'address', 'is_active']));

            // Sincronizar especialidades (sync borra las viejas y pone las nuevas)
            $doctor->specialties()->sync($request->specialties);

            DB::commit();
            return redirect()->route('doctors.index')->with('status', 'Doctor actualizado.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}

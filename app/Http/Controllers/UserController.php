<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        // Listamos usuarios que NO sean médicos (para no mezclarlos)
        // O simplemente listamos todos los que tienen roles administrativos
        $users = User::role(['admin', 'director_tecnico', 'contable'])->get();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::whereIn('name', ['admin', 'director_tecnico', 'contable'])->get();
        return view('admin.users.create', compact('roles'));
    }

public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed',Password::defaults()],
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($request->role);

        return redirect()->route('admin.users.index')->with('success', 'Usuario administrativo creado correctamente.');
    }

    public function edit(User $user)
    {
        $roles = Role::whereIn('name', ['admin', 'director_tecnico', 'contable'])->get();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|exists:roles,name',
        ]);

        $user->update($request->only('name', 'email'));

        if ($request->filled('password')) {
            $request->validate(['password' => ['confirmed', Password::defaults()]]);
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->syncRoles($request->role);

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminarte a ti mismo.');
        }
        $user->delete();
        return back()->with('success', 'Usuario eliminado.');
    }
}

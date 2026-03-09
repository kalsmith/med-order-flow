<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Aquí podrías pasar estadísticas luego (ej: conteo de órdenes, doctores, etc.)
        return view('admin.dashboard');
    }
}

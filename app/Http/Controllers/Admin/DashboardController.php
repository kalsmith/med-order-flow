<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Order;
use App\Models\PayoutRequest;
use App\Models\Prescription;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * admin.panel -> view('admin.dashboard')
     */
    public function index()
    {
        $todayOrders = Order::whereDate('created_at', today())->count();
        $pendingSignature = Order::where('status', 'paid')->whereNull('signed_at')->count();

        return view('admin.dashboard', compact('todayOrders', 'pendingSignature'));
    }

    /**
     * admin.accounting.index -> view('admin.accounting.index')
     */

    public function reports()
    {
        // 1. Estadísticas Globales
        $totalRevenue = Order::where('status', 'paid')->sum('amount');

        // Calculamos cuánto se ha pagado en total a todos los médicos
        $totalAlreadyPaid = PayoutRequest::where('status', 'paid')->sum('amount');

        // Calculamos cuánto han generado en total todos los médicos (Honorarios totales)
        $totalGeneratedByDoctors = Prescription::where('status', 'signed')->count() * 1500;

        $stats = [
            'total_revenue'  => $totalRevenue,
            'total_to_pay'   => $totalGeneratedByDoctors - $totalAlreadyPaid, // Deuda real global
            'pending_orders' => Order::where('status', 'paid')->whereNull('signed_at')->count(),
        ];

        // 2. Reporte Detallado por Médico
        $doctorReports = Doctor::with(['user'])->get()->map(function ($doctor) {

            // Lo que ha firmado (Histórico)
            $signedCount = Prescription::where('doctor_id', $doctor->id)
                ->where('status', 'signed')
                ->count();

            // Ingresos brutos de las órdenes que él firmó
            $orderIds = Prescription::where('doctor_id', $doctor->id)
                ->where('status', 'signed')
                ->pluck('order_id');

            $grossRevenue = Order::whereIn('id', $orderIds)->sum('amount');

            // Cálculo de Honorarios
            $historicEarnings = $signedCount * 1500;

            // Lo que YA se le pagó (según tabla payout_requests)
            $alreadyPaid = PayoutRequest::where('doctor_id', $doctor->id)
                ->where('status', 'paid')
                ->sum('amount');

            $flowFees = $grossRevenue * 0.038;

            return [
                'id'               => $doctor->id,
                'name'             => ($doctor->prefix ?? 'Dr.') . ' ' . $doctor->user->name,
                'signed_count'     => $signedCount,
                'gross_revenue'    => $grossRevenue,
                'historic_earning' => $historicEarnings,
                'current_balance'  => $historicEarnings - $alreadyPaid, // SALDO DINÁMICO
                'total_paid_out'   => $alreadyPaid,
                'flow_fees'        => $flowFees,
                'net_platform'     => $grossRevenue - $historicEarnings - $flowFees,
            ];
        });

        return view('admin.accounting.index', compact('stats', 'doctorReports'));
    }



    /**
     * admin.reports -> view('admin.reports')
     */
    public function businessReports()
    {
        $orderStats = Order::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        $popularExams = Order::whereNotNull('exam_type_id')
            ->select('exam_type_id', DB::raw('count(*) as total'))
            ->with('examType')
            ->groupBy('exam_type_id')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();

        // IMPORTANTE: Ruta de vista ajustada
        return view('admin.reports', compact('orderStats', 'popularExams'));
    }
}

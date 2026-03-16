<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Order;
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
        $stats = [
            'total_revenue'  => Order::where('status', 'paid')->sum('amount'),
            // La deuda real es lo que hay en los balances de los médicos ahora mismo
            'total_to_pay'   => Doctor::sum('balance'),
            'pending_orders' => Order::where('status', 'paid')->whereNull('signed_at')->count(),
        ];

        $doctorReports = Doctor::with(['user'])->get()->map(function ($doctor) {
            // 1. Rendimiento Histórico (Todo lo que ha firmado)
            $prescriptions = Prescription::where('doctor_id', $doctor->id)
                ->where('status', 'signed')
                ->get();

            $orderIds = $prescriptions->pluck('order_id');
            $grossRevenue = Order::whereIn('id', $orderIds)->sum('amount');
            $historicEarnings = $prescriptions->count() * 1500;

            // 2. Pagos ya realizados (Para saber cuánto se ha llevado)
            $totalPaidOut = PayoutRequest::where('doctor_id', $doctor->id)
                ->where('status', 'paid')
                ->sum('amount');

            $flowFees = $grossRevenue * 0.038;

            return [
                'id'               => $doctor->id,
                'name'             => $doctor->prefix . ' ' . $doctor->user->name,
                'signed_count'     => $prescriptions->count(),
                'gross_revenue'    => $grossRevenue,
                'historic_earning' => $historicEarnings, // Lo que ganó en total
                'current_balance'  => $doctor->balance,   // Lo que le queda por cobrar (Saldo 0 si retiró)
                'total_paid_out'   => $totalPaidOut,     // Lo que ya se le transfirió
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

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
            'total_revenue' => Order::where('status', 'paid')->sum('amount'),
            'total_to_pay'  => Prescription::where('status', 'signed')->count() * 1500,
            'pending_orders' => Order::where('status', 'paid')->whereNull('signed_at')->count(),
        ];

        $doctorReports = Doctor::with(['user'])->get()->map(function ($doctor) {
            $prescriptions = Prescription::where('doctor_id', $doctor->id)
                ->where('status', 'signed')
                ->get();

            $orderIds = $prescriptions->pluck('order_id');
            $grossRevenue = Order::whereIn('id', $orderIds)->sum('amount');
            $payoutDoctor = $prescriptions->count() * 1500;
            $flowFees = $grossRevenue * 0.038;

            return [
                'name'           => $doctor->prefix . ' ' . $doctor->user->name,
                'signed_count'   => $prescriptions->count(),
                'gross_revenue'  => $grossRevenue,
                'payout_doctor'  => $payoutDoctor,
                'flow_fees'      => $flowFees,
                'net_platform'   => $grossRevenue - $payoutDoctor - $flowFees,
            ];
        });

        // IMPORTANTE: Ruta de vista ajustada
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

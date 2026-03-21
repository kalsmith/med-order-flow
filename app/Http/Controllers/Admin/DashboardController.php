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
    // 1. Recaudación Total (Lo que entró por pasarela de pago)
    $totalRevenue = Order::where('status', 'paid')->sum('amount');

    // 2. Lo que ya se ha transferido efectivamente a los médicos
    $totalAlreadyPaid = PayoutRequest::where('status', 'paid')->sum('amount');

    // 3. Cálculo Global de lo generado por firmas (Deuda histórica total)
    $totalGeneratedByDoctors = Prescription::where('status', 'signed')
        ->selectRaw("SUM(CASE WHEN type = 'custom' THEN 2800 ELSE 1800 END) as total")
        ->value('total') ?? 0;

    $stats = [
        'total_revenue'  => $totalRevenue,
        'total_to_pay'   => $totalGeneratedByDoctors - $totalAlreadyPaid, // Deuda pendiente actual
        'pending_orders' => Order::where('status', 'paid')->whereNull('signed_at')->count(),
    ];

    $doctorReports = Doctor::with(['user'])->get()->map(function ($doctor) {

        // Conteo por tipo para este médico
        $counts = Prescription::where('doctor_id', $doctor->id)
            ->where('status', 'signed')
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN type = 'custom' THEN 1 ELSE 0 END) as custom_count,
                SUM(CASE WHEN type = 'standard' THEN 1 ELSE 0 END) as standard_count
            ")->first();

        // Ingresos brutos generados por las órdenes de este médico
        $orderIds = Prescription::where('doctor_id', $doctor->id)
            ->where('status', 'signed')
            ->pluck('order_id');

        $grossRevenue = Order::whereIn('id', $orderIds)->sum('amount');

        // Honorarios que le corresponden al médico
        $historicEarnings = ($counts->custom_count * 2800) + ($counts->standard_count * 1800);

        // Lo que ya se le pagó a este médico
        $alreadyPaid = PayoutRequest::where('doctor_id', $doctor->id)
            ->where('status', 'paid')
            ->sum('amount');

        return [
            'id'               => $doctor->id,
            'name'             => ($doctor->prefix ?? 'Dr.') . ' ' . $doctor->user->name,
            'signed_count'     => $counts->total,
            'gross_revenue'    => $grossRevenue,
            'historic_earning' => $historicEarnings,
            'current_balance'  => $historicEarnings - $alreadyPaid,
            'total_paid_out'   => $alreadyPaid,
            'net_platform'     => $grossRevenue - $historicEarnings, // Utilidad antes de gastos externos
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



    // En DashboardController.php

public function clinicalQualityReports()
{
    // 1. Estadísticas básicas (Conteo de hoy y globales)
    $stats = [
        'signed_today' => Prescription::where('status', 'signed')
            ->whereDate('signed_at', today())
            ->count(),
        'rejected_orders' => Order::where('status', 'rejected')->count(),
        'voided_prescriptions' => Prescription::where('status', 'voided')->count(),
    ];

    // 2. Rendimiento Médico (Basado en Prescriptions según tu SQL)
    $doctorPerformance = Doctor::with('user')
        ->get()
        ->map(function ($doctor) {
            // Contamos sobre la tabla prescriptions donde sí hay data en tu SQL
            $total = Prescription::where('doctor_id', $doctor->id)->count();
            $signed = Prescription::where('doctor_id', $doctor->id)
                ->where('status', 'signed')
                ->count();

            return [
                'name' => ($doctor->prefix ?? 'Dr.') . ' ' . ($doctor->user->name ?? 'N/A'),
                'total' => $total,
                'signed' => $signed,
                'efficiency' => $total > 0 ? round(($signed / $total) * 100) : 0,
            ];
        });

    // 3. Últimas órdenes (Filtrado por pagadas para auditar)
    // Quitamos 'whereHas' de paciente si quieres ver incluso los errores (opcional)
    // pero mantenemos la carga de relaciones para evitar N+1
    $latestOrders = Order::with(['doctor.user', 'patient', 'activePrescription'])
        ->whereIn('status', ['paid', 'pending']) // Ajustado según tu SQL que tiene muchos pending
        ->latest()
        ->take(5)
        ->get();

    return view('admin.reports.clinical', compact('stats', 'doctorPerformance', 'latestOrders'));
}
}

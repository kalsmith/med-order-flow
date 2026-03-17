@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Supervisión de Órdenes Médicas (DT)</h2>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Paciente</th>
                        <th>Médico</th>
                        <th>Examen / Descripción</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                    <tr>
                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <strong>{{ $order->patient->full_name ?? 'N/A' }}</strong><br>
                            <small class="text-muted">{{ $order->patient->rut ?? '' }}</small>
                        </td>
                        <td>{{ $order->doctor->name ?? 'Pendiente' }}</td>
                        <td>{{ $order->display_name }}</td>
                        <td>
                            <span class="badge bg-{{ $order->status == 'paid' ? 'primary' : 'secondary' }}">
                                {{ strtoupper($order->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Auditar
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{-- Aquí el método links() funcionará perfecto porque usamos paginate() --}}
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection

@extends('layouts.admin')

@section('header', 'Nueva Orden Médica')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form action="{{ route('admin.orders.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">1. Seleccione Especialidad</label>
                    <select id="specialty_id" name="specialty_id" class="form-select" required>
                        <option value="">Seleccione una especialidad...</option>
                        @foreach($specialties as $specialty)
                            <option value="{{ $specialty->id }}">{{ $specialty->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">2. Tipo de Examen</label>
                    <select id="exam_type_id" name="exam_type_id" class="form-select" disabled required>
                        <option value="">Primero seleccione especialidad...</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Costo Examen ($)</label>
                    <input type="number" id="amount" name="amount" class="form-control" readonly>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('specialty_id').addEventListener('change', function() {
    const specialtyId = this.value;
    const examSelect = document.getElementById('exam_type_id');
    const amountInput = document.getElementById('amount');

    // Resetear
    examSelect.innerHTML = '<option value="">Cargando exámenes...</option>';
    examSelect.disabled = true;
    amountInput.value = '';

    if (!specialtyId) return;

    // Llamada a nuestra mini-api
    fetch(`/api/specialties/${specialtyId}/exam-types`)
        .then(response => response.json())
        .then(data => {
            examSelect.innerHTML = '<option value="">Seleccione el examen...</option>';
            data.forEach(exam => {
                const option = document.createElement('option');
                option.value = exam.id;
                option.textContent = exam.name;
                option.dataset.price = exam.base_price; // Guardamos el precio en un dataset
                examSelect.appendChild(option);
            });
            examSelect.disabled = false;
        });
});

// Auto-poblar el precio al elegir examen
document.getElementById('exam_type_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    document.getElementById('amount').value = selectedOption.dataset.price || 0;
});
</script>
@endsection

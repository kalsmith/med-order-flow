@props(['value' => '', 'name' => 'tax_id', 'label' => 'RUT'])

<div class="mb-3">
    <label class="form-label fw-bold">{{ $label }}</label>
    <input
        type="text"
        name="{{ $name }}"
        id="rut_field"
        class="form-control form-control-lg rounded-4"
        value="{{ $value }}"
        placeholder="12.345.678-K"
        oninput="formatRut(this)"
    >
</div>

<script>
function formatRut(input) {
    let value = input.value.replace(/[^\dkK]/g, '');
    if (value.length > 1) {
        let dv = value.slice(-1).toUpperCase();
        let number = value.slice(0, -1);
        let formatted = number.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        input.value = formatted + '-' + dv;
    }
}
</script>

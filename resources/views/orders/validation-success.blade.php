<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validación de Orden Médica</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="bg-green-500 p-4 text-center">
            <svg class="w-16 h-16 text-white mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h1 class="text-white font-bold text-xl mt-2">Orden Médica Auténtica</h1>
        </div>

        <div class="p-6 space-y-4">
            <div>
                <p class="text-xs text-gray-500 uppercase font-semibold">Paciente</p>
                <p class="text-lg font-medium text-gray-900">{{ $order->patient->full_name }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold">RUT Paciente</p>
                    <p class="text-gray-900">{{ $order->patient->rut }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold">Fecha Emisión</p>
                    <p class="text-gray-900">{{ $order->created_at->format('d/m/Y') }}</p>
                </div>
            </div>

            <hr class="border-gray-100">

            <div>
                <p class="text-xs text-gray-500 uppercase font-semibold">Médico Emisor</p>
                <p class="text-gray-900">Dr. {{ $order->doctor->user->name }}</p>
                <p class="text-xs text-gray-500">RUT: {{ $order->doctor->rut }} | S.I.S: {{ $order->doctor->rnpi_number }}</p>
            </div>

            <div class="bg-blue-50 p-3 rounded-lg">
                <p class="text-xs text-blue-700 font-bold uppercase mb-1">Estado del Documento</p>
                <span class="px-2 py-1 bg-blue-200 text-blue-800 text-xs font-bold rounded-full">FIRMADO ELECTRÓNICAMENTE</span>
            </div>
        </div>

        <div class="bg-gray-50 p-4 text-center">
            <p class="text-xs text-gray-400">Esta validación es generada automáticamente por Doctor 911</p>
        </div>
    </div>
</body>
</html>

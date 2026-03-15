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
        <div class="bg-emerald-500 p-6 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white bg-opacity-20 rounded-full mb-3">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h1 class="text-white font-bold text-xl">Documento Auténtico</h1>
            <p class="text-emerald-100 text-sm">Verificación exitosa en PideTuExamen.cl</p>
        </div>

        <div class="p-6 space-y-5">
            <section>
                <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold mb-1">Paciente</p>
                <p class="text-lg font-bold text-gray-800 leading-tight">{{ strtoupper($order->patient->full_name) }}</p>
                <div class="flex gap-4 mt-1 text-sm text-gray-600">
                    <span><strong>RUT:</strong> {{ $order->patient->rut }}</span>
                    <span><strong>Edad:</strong> {{ $order->patient->age }} años</span>
                </div>
            </section>

            <hr class="border-gray-100">

            <section>
                <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold mb-2">Análisis Solicitados</p>
                <div class="bg-blue-50 border-l-4 border-blue-500 p-3 rounded-r-lg">
                    <p class="text-blue-700 font-bold text-sm mb-1">
                        {{ $order->examType->name ?? ($order->type === 'custom' ? 'Orden Médica Personalizada' : 'Examen Estándar') }}
                    </p>

                    @if($order->type === 'custom')
                        <p class="text-gray-700 text-xs italic leading-relaxed">
                            {{ $order->activePrescription->clinical_context }}
                        </p>
                    @elseif($order->examType && $order->examType->children->isNotEmpty())
                        <ul class="text-xs text-gray-600 space-y-1 mt-2">
                            @foreach($order->examType->children as $child)
                                <li class="flex items-start">
                                    <span class="text-blue-500 mr-1">•</span> {{ $child->name }}
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </section>

            <section class="grid grid-cols-1 gap-3 pt-2">
                <div>
                    <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold mb-1">Médico Emisor</p>
                    <p class="text-gray-800 font-semibold text-sm">Dr(a). {{ $order->activePrescription->doctor->user->name }}</p>
                    <p class="text-[11px] text-gray-500">
                        RUT: {{ $order->activePrescription->doctor->rut }} | SIS: {{ $order->activePrescription->doctor->rnpi_number }}
                    </p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold mb-1">Fecha de Firma</p>
                    <p class="text-gray-800 text-sm">{{ $order->activePrescription->signed_at->format('d/m/Y H:i') }} hrs.</p>
                </div>
            </section>

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 flex items-center justify-between">
                <div>
                    <p class="text-[10px] text-blue-600 font-bold uppercase tracking-tighter">Estado de Integridad</p>
                    <p class="text-[11px] text-gray-600">Firma electrónica válida (Ley 19.799)</p>
                </div>
                <svg class="w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
            </div>
        </div>

        <div class="bg-gray-100 p-4 text-center border-t border-gray-200">
            <p class="text-[10px] text-gray-500 leading-tight">
                Generado automáticamente por <strong>PideTuExamen.cl</strong><br>
                ID Validación: {{ $order->activePrescription->verification_code }}
            </p>
        </div>
    </div>
</body>
</html>

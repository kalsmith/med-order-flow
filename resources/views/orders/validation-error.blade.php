<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden No Encontrada</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-md w-full bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="bg-red-500 p-6 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white bg-opacity-20 rounded-full mb-3">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h1 class="text-white font-bold text-xl">Documento No Encontrado</h1>
            <p class="text-red-100 text-sm">El código de verificación no es válido</p>
        </div>

        <div class="p-8 text-center space-y-4">
            <p class="text-gray-600">
                Lo sentimos, no hemos podido encontrar ninguna orden médica asociada al código:
                <span class="block font-mono font-bold text-gray-800 mt-2 text-lg">{{ $code }}</span>
            </p>

            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 text-left">
                <p class="text-xs text-yellow-700 leading-relaxed">
                    <strong>Sugerencias:</strong><br>
                    • Verifique que el enlace sea el correcto.<br>
                    • Asegúrese de haber escrito bien el código manual.<br>
                    • Si el problema persiste, contacte a soporte@pidetuexamen.cl
                </p>
            </div>

            <a href="https://pidetuexamen.cl"
               class="inline-block w-full bg-gray-800 text-white font-bold py-3 rounded-lg hover:bg-gray-700 transition">
                Ir al Inicio
            </a>
        </div>

        <div class="bg-gray-50 p-4 text-center border-t border-gray-200">
            <p class="text-[10px] text-gray-400">
                Seguridad PideTuExamen.cl &bull; {{ date('Y') }}
            </p>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Med Order Flow') }}</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        <style>
            body {
                font-family: 'Inter', sans-serif;
                background-color: #f8f9fa;
                background-image: radial-gradient(#dee2e6 0.5px, transparent 0.5px);
                background-size: 20px 20px;
            }
            .btn-primary { background-color: #0d6efd; border: none; transition: all 0.3s ease; }
            .btn-primary:hover { background-color: #0b5ed7; transform: translateY(-1px); }
            .card { border-radius: 1rem; }
        </style>

        @livewireStyles
    </head>
    <body>

        <main>
            {{-- CAMBIO AQUÍ: Usamos yield en lugar de slot --}}
            @yield('content')
        </main>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        @livewireScripts
    </body>
</html>

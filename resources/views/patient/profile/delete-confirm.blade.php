@extends('layouts.front')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-danger shadow">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="fas fa-exclamation-triangle text-danger fa-4x"></i>
                    </div>
                    <h2 class="fw-bold">¿Eliminar tu cuenta?</h2>
                    <p class="text-muted">
                        Esta acción desactivará tu acceso a <strong>PideTuExamen</strong>.
                        Tus órdenes médicas generadas anteriormente seguirán siendo válidas, pero ya no podrás descargarlas desde este portal.
                    </p>

                    <form action="{{ route('profile.delete.execute') }}" method="POST" class="mt-4">
                        @csrf
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-danger btn-lg">
                                Confirmar y eliminar cuenta
                            </button>
                            <a href="{{ route('home') }}" class="btn btn-light">
                                Cancelar, prefiero quedarme
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.front')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            {{-- Llamada al componente Livewire --}}
            @livewire('patient.account-deletion')
        </div>
    </div>
</div>
@endsection

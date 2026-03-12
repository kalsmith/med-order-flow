@extends('layouts.admin')

@section('header')
    {{ auth()->user()->hasRole('doctor') ? 'Panel de Órdenes Médicas' : 'Gestión Global de Órdenes' }}
@endsection

@section('content')
    {{-- Llamada al componente Livewire --}}
    @livewire('medical-orders-table')
@endsection

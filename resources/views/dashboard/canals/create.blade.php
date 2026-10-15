@php
    $seo = ['title' => 'Nový kanál', 'noindex' => true];
@endphp
@extends('layouts.dashboard')

@section('content')
    <x-dashboard.frame fold>
        <x-dashboard.header heading="Nový kanál">
            <x-slot name="lead">Vyplňte údaje o kanáli. Polia označené <span class="ar-req">*</span> sú povinné.</x-slot>
        </x-dashboard.header>

        {{-- Rovnaký formulár ako úprava kanála; `simple` skryje polia, ktoré
             pri založení z nástenky nastavuje až administrátor. --}}
        @include('dashboard.canals._form', ['simple' => true])
    </x-dashboard.frame>
@endsection

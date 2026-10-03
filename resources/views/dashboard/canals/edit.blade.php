@extends('layouts.dashboard')

@section('title')
    <title>{{ "Upraviť kanál {$canal->title}" }} | Hlas Cirkvi</title>
@endsection

@section('content')



    <x-dashboard.frame>
        <x-dashboard.header :heading="$canal->title">
            <x-slot name="lead">Úprava údajov kanála. Polia označené <span class="ar-req">*</span> sú povinné.</x-slot>
            <x-slot name="actions">
                <a href="{{ route('organizations.show', $canal) }}" class="ar-btn ar-btn--quiet" target="_blank" rel="noopener">
                    <i class="ph ph-arrow-square-out"></i> Zobraziť kanál
                </a>
                <a href="{{ route('profile.canals.index') }}" class="ar-btn ar-btn--quiet">
                    <i class="ph ph-arrow-left"></i> Späť na kanály
                </a>
            </x-slot>
        </x-dashboard.header>

        @include('dashboard.canals._form')

    </x-dashboard.frame>

@endsection

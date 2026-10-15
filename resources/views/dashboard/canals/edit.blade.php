@php
    $seo = ['title' => "Upraviť kanál {$canal->title}", 'noindex' => true];
@endphp
@extends('layouts.dashboard')

@section('content')
    <x-dashboard.frame fold>
        <x-dashboard.header :heading="$canal->title">
            <x-slot name="lead">Úprava údajov kanála. Polia označené <span class="ar-req">*</span> sú povinné.</x-slot>
            <x-slot name="actions">
                <a href="{{ route('organizations.show', $canal) }}" class="ar-btn ar-btn--quiet" target="_blank" rel="noopener">
                    <i class="ph ph-arrow-square-out"></i> Zobraziť kanál
                </a>
            </x-slot>
        </x-dashboard.header>

        @include('dashboard.canals._form')

    </x-dashboard.frame>

@endsection

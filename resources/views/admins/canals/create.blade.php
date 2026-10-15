@extends('layouts.admin')

@php
    $seo = ['title' => 'Nový kanál · Administrácia', 'noindex' => true];
@endphp

@section('content')
    <x-pages.admin>
        <x-slot name="title">Nový kanál</x-slot>
        <x-slot name="lead">Vyplňte údaje o kanáli. Polia označené <span class="ar-req">*</span> sú povinné.</x-slot>
        <x-slot name="page">
            @include('dashboard.canals._form')
        </x-slot>
    </x-pages.admin>
@endsection

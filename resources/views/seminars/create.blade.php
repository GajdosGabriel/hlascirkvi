@php
    $seo = ['title' => 'Nová kolekcia', 'noindex' => true];
@endphp
@extends('layouts.dashboard')

@section('content')
    <x-dashboard.frame fold>
        <x-dashboard.header heading="Nová kolekcia">
            <x-slot name="lead">Kolekcia kanála {{ $canal->title }}. Polia označené <span class="ar-req">*</span> sú povinné.</x-slot>
        </x-dashboard.header>

        <form method="POST" action="{{ route('profile.canals.seminars.store', $canal->id) }}" class="max-w-3xl">
            @csrf
            @include('seminars.form', ['submitLabel' => 'Vytvoriť kolekciu'])
        </form>
    </x-dashboard.frame>
@endsection

@php
    $seo = ['title' => 'Upraviť modlitbu', 'noindex' => true];
@endphp
@extends('layouts.dashboard')

@section('content')
    <x-dashboard.frame fold>
        <x-dashboard.header heading="Upraviť modlitbu">
            <x-slot name="lead">Modlitebná prosba kanála {{ $canal->title }}. Polia označené <span class="ar-req">*</span> sú povinné.</x-slot>
        </x-dashboard.header>

        <form action="{{ route('profile.canals.prayers.update', [$canal->id, $prayer->id]) }}" method="post" class="max-w-3xl">
            @csrf @method('PUT')
            @include('prayers._form')
        </form>
    </x-dashboard.frame>
@endsection

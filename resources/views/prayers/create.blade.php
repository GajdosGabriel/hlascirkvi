@php
    $seo = ['title' => 'Nová modlitba', 'noindex' => true];
@endphp
@extends('layouts.dashboard')

@section('content')
    <x-dashboard.frame fold>
        <x-dashboard.header heading="Nová modlitba">
            <x-slot name="lead">Modlitebná prosba kanála {{ $canal->title }}. Polia označené <span class="ar-req">*</span> sú povinné.</x-slot>
        </x-dashboard.header>

        {{-- Kanál z adresy, nie aktívny kanál užívateľa — inak sa modlitba
             založená z iného spravovaného kanála uložila do aktívneho. --}}
        <form action="{{ route('profile.canals.prayers.store', $canal->id) }}" method="post" class="max-w-3xl">
            @csrf
            @include('prayers._form')
        </form>
    </x-dashboard.frame>
@endsection

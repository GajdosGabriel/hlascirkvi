@extends('layouts.dashboard')

@section('title')
    <title>Nová kolekcia</title>
@endsection

@section('content')
    <x-dashboard.frame>
        <x-dashboard.header heading="Nová kolekcia">
            <x-slot name="lead">Vyplňte údaje o kolekcii pre kanál {{ $canal->title }}. Polia označené * sú povinné.</x-slot>
            <x-slot name="actions">
                <a class="btn btn-default" href="{{ route('profile.canals.seminars.index', $canal->id) }}">Späť na kolekcie</a>
            </x-slot>
        </x-dashboard.header>

        <form method="POST" action="{{ route('profile.canals.seminars.store', $canal->id) }}" class="space-y-6">
            @csrf
            @include('seminars.form', ['submitLabel' => 'Vytvoriť kolekciu'])
        </form>
    </x-dashboard.frame>
@endsection

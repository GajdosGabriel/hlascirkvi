@extends('layouts.dashboard')

@section('title')
    <title>Nový seminár</title>
@endsection

@section('content')
    <x-dashboard.frame>
        <x-dashboard.header heading="Nový seminár">
            <x-slot name="lead">Vyplňte údaje o seminári pre kanál {{ $canal->title }}. Polia označené * sú povinné.</x-slot>
            <x-slot name="actions">
                <a class="btn btn-default" href="{{ route('profile.canals.seminars.index', $canal->id) }}">Späť na semináre</a>
            </x-slot>
        </x-dashboard.header>

        <form method="POST" action="{{ route('profile.canals.seminars.store', $canal->id) }}" class="space-y-6">
            @csrf
            @include('seminars.form', ['submitLabel' => 'Vytvoriť seminár'])
        </form>
    </x-dashboard.frame>
@endsection

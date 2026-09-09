@extends('layouts.dashboard')

@section('title')
    <title>Nový seminár</title>
@endsection

@section('content')
    <x-dashboard.frame>
        <x-dashboard.header heading="Nový seminár">
            <x-slot name="lead">Vyplňte údaje o seminári pre kanál {{ $organization->title }}. Polia označené * sú povinné.</x-slot>
            <x-slot name="actions">
                <a class="btn btn-default" href="{{ route('profile.organization.seminar.index', $organization->id) }}">Späť na semináre</a>
            </x-slot>
        </x-dashboard.header>

        <form method="POST" action="{{ route('profile.organization.seminar.store', $organization->id) }}" class="space-y-6">
            @csrf
            @include('seminars.form', ['submitLabel' => 'Vytvoriť seminár'])
        </form>
    </x-dashboard.frame>
@endsection

@extends('layouts.dashboard')

@section('title')
    <title>{{ "Semináre {$organization->title}" }}</title>
@endsection

@section('content')

    @php
        $plural = fn (int $n, string $one, string $few, string $many)
            => $n === 1 ? $one : ($n >= 2 && $n <= 4 ? $few : $many);

        $total = $seminars->count();
    @endphp

    <x-dashboard.shell :organization="$organization" section="seminars" heading="Semináre">

        <x-slot name="lead">
            {{ number_format($total, 0, ',', ' ') }}
            {{ $plural($total, 'seminár', 'semináre', 'seminárov') }} kanála
        </x-slot>

        <x-slot name="actions">
            <a href="{{ route('profile.organization.seminar.create', $organization->id) }}" class="ar-btn ar-btn--accent">
                <i class="fas fa-plus"></i> Nový seminár
            </a>
        </x-slot>

        <x-dashboard.panel title="Semináre kanála" flush>


            @include('profiles.seminars._list')
        </x-dashboard.panel>

    </x-dashboard.shell>
@endsection

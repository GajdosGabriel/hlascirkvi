@extends('layouts.dashboard')

@php
    $seo = ['title' => "Kolekcie a semináre {$canal->title}", 'noindex' => true];
@endphp

@section('content')

    @php
        $plural = fn (int $n, string $one, string $few, string $many)
            => $n === 1 ? $one : ($n >= 2 && $n <= 4 ? $few : $many);

        $total = $seminars->count();
    @endphp

    <x-dashboard.shell :canal="$canal" section="seminars" heading="Kolekcie a semináre">

        <x-slot name="lead">
            {{ number_format($total, 0, ',', ' ') }}
            {{ $plural($total, 'kolekcia alebo seminár', 'kolekcie a semináre', 'kolekcií a seminárov') }} kanála
        </x-slot>

        <x-slot name="actions">
            <a href="{{ route('profile.canals.seminars.create', $canal->id) }}" class="ar-btn ar-btn--accent">
                <i class="ph ph-plus"></i> Nová kolekcia
            </a>
        </x-slot>

        <x-dashboard.panel flush>
            @include('profiles.seminars._list')
        </x-dashboard.panel>

    </x-dashboard.shell>
@endsection

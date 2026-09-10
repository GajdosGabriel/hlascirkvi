@extends('layouts.dashboard')

@section('title')
    <title>Vaše kanály</title>
@endsection

@section('headerCSS')
    {{-- Rovnaké písmo ako verejná časť. Layout ho nenačítava globálne. --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
@endsection

@php
    $plural = fn (int $n, string $one, string $few, string $many)
        => $n === 1 ? $one : ($n >= 2 && $n <= 4 ? $few : $many);
@endphp

@section('content')

    <x-dashboard.frame>
    <x-dashboard.header heading="Vaše kanály">


        <x-slot name="lead">
            {{ $canals->total() }}
            {{ $plural($canals->total(), 'kanál', 'kanály', 'kanálov') }}
            @if ($canals->total())
                · príspevky sa zapisujú do kanála s aktívnym prihlásením
            @endif
        </x-slot>
        <x-slot name="actions">
            <a class="btn btn-primary" href="{{ route('profile.canals.create') }}">Nový kanál</a>
        </x-slot>
    </x-dashboard.header>

    {{-- Prepínače zodpovedajú kľúčom v App\Filters\CanalFilters. --}}
    <x-filters.bar class="mb-5"
                   :filters="['unpublished', 'deletedAt' => 'Zrušené']"
                   search="Hľadať kanál" />

    <x-canal.list :canals="$canals" />

    @if ($canals->hasPages())
        <div class="mt-8">
            {{ $canals->links() }}
        </div>
    @endif

    </x-dashboard.frame>
@endsection

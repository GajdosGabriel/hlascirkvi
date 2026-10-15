@php
    $seo = ['title' => 'Vaše kanály', 'noindex' => true];
@endphp
@extends('layouts.dashboard')


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
            <a class="ar-btn ar-btn--accent" href="{{ route('profile.canals.create') }}"><i class="ph ph-plus"></i> Nový kanál</a>
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

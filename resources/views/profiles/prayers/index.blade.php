@extends('layouts.app')

@section('title')
    <title>{{ "Modlitby {$organization->title}" }}</title>
@endsection

@section('body-class', 'ar-body')

@section('headerCSS')
    @include('partials.dashboard-head')
@endsection

@section('content')

    @php
        $plural = fn (int $n, string $one, string $few, string $many)
            => $n === 1 ? $one : ($n >= 2 && $n <= 4 ? $few : $many);

        $total = $prayers->total();
    @endphp

    <x-dashboard.shell :organization="$organization" section="prayers" heading="Modlitby">

        <x-slot name="lead">
            {{ number_format($total, 0, ',', ' ') }}
            {{ $plural($total, 'modlitba', 'modlitby', 'modlitieb') }} kanála
        </x-slot>

        <x-slot name="actions">
            <a href="{{ route('profile.organization.prayer.create', $organization->id) }}" class="ar-btn ar-btn--accent">
                <i class="fas fa-plus"></i> Nová modlitba
            </a>
        </x-slot>

        <section class="ar-panel">
            <header class="ar-panel__head">
                <h2 class="ar-panel__title">Modlitby kanála</h2>
                <span class="ar-panel__note">
                    @if ($prayers->hasPages())
                        strana {{ $prayers->currentPage() }} z {{ $prayers->lastPage() }}
                    @endif
                </span>
            </header>

            @forelse ($prayers as $prayer)
                <article class="ar-item">
                    <div class="ar-item__body">
                        <span class="ar-item__title">{{ $prayer->title }}</span>

                        <p class="mt-1 text-sm text-[color:var(--ar-ink-soft)]">
                            {{ \Illuminate\Support\Str::limit(strip_tags($prayer->body), 220) }}
                        </p>

                        <div class="ar-item__meta">
                            <span><i class="far fa-user"></i>{{ $prayer->user_name ?: 'návštevník' }}</span>
                            <time datetime="{{ $prayer->created_at->toIso8601String() }}">
                                {{ $prayer->created_at->locale('sk')->isoFormat('D. M. YYYY') }}
                            </time>
                        </div>

                        @if ($prayer->fulfilled_at)
                            <div class="ar-item__tags">
                                <span class="ar-badge ar-badge--ok">Vypočuté</span>
                            </div>
                        @endif
                    </div>

                    <div class="ar-item__actions">
                        <dropdown-slot>
                            <a href="{{ route('profile.organization.prayer.edit', [$organization->id, $prayer->id]) }}"
                               class="ar-act">
                                <i class="fas fa-pen text-[.7rem]"></i> Upraviť
                            </a>

                        </dropdown-slot>
                    </div>
                </article>
            @empty
                <p class="ar-empty">Kanál zatiaľ nemá žiadnu modlitbu.</p>
            @endforelse
        </section>

        @if ($prayers->hasPages())
            <div class="mt-8">
                {{ $prayers->links() }}
            </div>
        @endif

    </x-dashboard.shell>
@endsection

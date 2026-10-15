@extends('layouts.dashboard')

@php
    $seo = ['title' => "Modlitby {$canal->title}", 'noindex' => true];
@endphp

@section('content')

    @php
        $plural = fn (int $n, string $one, string $few, string $many)
            => $n === 1 ? $one : ($n >= 2 && $n <= 4 ? $few : $many);

        $total = $prayers->total();
    @endphp

    <x-dashboard.shell :canal="$canal" section="prayers" heading="Modlitby">

        <x-slot name="lead">
            {{ number_format($total, 0, ',', ' ') }}
            {{ $plural($total, 'modlitba', 'modlitby', 'modlitieb') }} kanála
            @if ($prayers->hasPages())
                <span class="mx-1.5 text-gray-300" aria-hidden="true">·</span> strana {{ $prayers->currentPage() }} z {{ $prayers->lastPage() }}
            @endif
        </x-slot>

        <x-slot name="actions">
            <a href="{{ route('profile.canals.prayers.create', $canal->id) }}" class="ar-btn ar-btn--accent">
                <i class="ph ph-plus"></i> Nová modlitba
            </a>
        </x-slot>

        <x-dashboard.panel flush>

            @forelse ($prayers as $prayer)
                <article class="ar-item">
                    <div class="ar-item__body">
                        <span class="ar-item__title">{{ $prayer->title }}</span>

                        <p class="mt-1 text-sm text-[color:var(--ar-ink-soft)]">
                            {{ \Illuminate\Support\Str::limit(strip_tags($prayer->body), 220) }}
                        </p>

                        <div class="ar-item__meta">
                            <span><i class="ph ph-user"></i>{{ $prayer->user_name ?: 'návštevník' }}</span>
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

                    {{-- Jediná akcia, preto priamy odkaz a nie ponuka. --}}
                    <div class="ar-item__actions">
                        <a class="ar-act" href="{{ route('profile.canals.prayers.edit', [$canal->id, $prayer->id]) }}">
                            <i class="ph ph-pencil-simple" aria-hidden="true"></i> Upraviť
                        </a>
                    </div>
                </article>
            @empty
                <x-dashboard.empty>Kanál zatiaľ nemá žiadnu modlitbu.</x-dashboard.empty>
            @endforelse
        </x-dashboard.panel>

        @if ($prayers->hasPages())
            <div class="mt-8">
                {{ $prayers->links() }}
            </div>
        @endif

    </x-dashboard.shell>
@endsection

@php
    $seo = ['title' => $ticket->subject . ' · Podpora', 'noindex' => true];
@endphp
@extends('layouts.dashboard')

@section('content')
    <x-pages.dashboard>
        <x-slot name="title">{{ $ticket->subject }}</x-slot>
        <x-slot name="title_right">
            <a href="{{ route('profile.support.index') }}" class="ar-tab">
                <i class="ph ph-arrow-left mr-1" aria-hidden="true"></i>Podpora
            </a>
        </x-slot>

        <x-slot name="page">
            <div class="mb-4 flex flex-wrap items-center gap-2 text-sm text-[color:var(--ar-muted)]">
                <x-dashboard.status-badge :label="$ticket->status->label()" :tone="$ticket->status->tone()" />
                <span>{{ $ticket->reference() }} · založené {{ $ticket->created_at?->format('j. n. Y') }}</span>
            </div>

            <x-dashboard.panel class="mb-6">
                @include('support._thread', ['staffView' => false])
            </x-dashboard.panel>

            <x-dashboard.panel title="{{ $ticket->status === \App\Enums\SupportStatus::Closed ? 'Znovu otvoriť odpoveďou' : 'Odpovedať' }}">
                <form method="POST" action="{{ route('profile.support.reply', $ticket) }}" class="space-y-3">
                    @csrf
                    <label class="sr-only" for="support-reply">Vaša odpoveď</label>
                    <textarea id="support-reply" name="body" rows="4" maxlength="5000" required class="ar-field"
                              @error('body') aria-invalid="true" @enderror>{{ old('body') }}</textarea>
                    @error('body')<p class="ar-error">{{ $message }}</p>@enderror
                    <button type="submit" class="ar-btn ar-btn--accent">
                        <i class="ph ph-paper-plane-tilt"></i> Odoslať
                    </button>
                </form>

                <x-slot name="footer">
                    <form method="POST" action="{{ route('profile.support.status', $ticket) }}">
                        @csrf
                        @method('PUT')
                        @if ($ticket->status === \App\Enums\SupportStatus::Closed)
                            <input type="hidden" name="status" value="open">
                            <button type="submit" class="ar-btn ar-btn--quiet">Znovu otvoriť</button>
                        @else
                            <input type="hidden" name="status" value="closed">
                            <button type="submit" class="ar-btn ar-btn--quiet">
                                <i class="ph ph-check"></i> Vyriešené, uzavrieť
                            </button>
                        @endif
                    </form>
                </x-slot>
            </x-dashboard.panel>
        </x-slot>
    </x-pages.dashboard>
@endsection

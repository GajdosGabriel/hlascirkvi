@php
    $seo = ['title' => 'Podpora', 'noindex' => true];
@endphp
@extends('layouts.dashboard')

@section('content')
    <x-pages.dashboard>
        <x-slot name="title">Podpora</x-slot>

        <x-slot name="page">
            <x-dashboard.panel title="Nová správa" class="mb-6">
                <x-slot name="note">Odpoveď príde sem aj na váš e-mail.</x-slot>

                @include('support._form', ['idPrefix' => 'support'])
            </x-dashboard.panel>

            <x-dashboard.panel title="Vaše správy" flush>
                @forelse ($tickets as $ticket)
                    <article class="ar-item">
                        <div class="ar-item__body">
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                <x-dashboard.status-badge :label="$ticket->status->label()" :tone="$ticket->status->tone()" />
                                @if ($ticket->user_seen_at === null)
                                    <x-dashboard.status-badge label="Nová odpoveď" tone="red" />
                                @endif
                                <span class="text-xs text-[color:var(--ar-muted)]">
                                    {{ $ticket->reference() }}
                                </span>
                            </div>
                            <a href="{{ route('profile.support.show', $ticket) }}" class="ar-item__title mt-1 block">{{ $ticket->subject }}</a>
                            <div class="ar-item__meta">
                                <span><i class="ph ph-chats"></i>{{ $ticket->messages_count }}</span>
                                <span title="{{ $ticket->last_activity_at?->format('j. n. Y H:i') }}">
                                    <i class="ph ph-clock"></i>{{ $ticket->last_activity_at?->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    </article>
                @empty
                    <x-dashboard.empty>Zatiaľ ste podpore nepísali.</x-dashboard.empty>
                @endforelse
            </x-dashboard.panel>

            @if ($tickets->hasPages())
                <div class="mt-8">{{ $tickets->links() }}</div>
            @endif
        </x-slot>
    </x-pages.dashboard>
@endsection

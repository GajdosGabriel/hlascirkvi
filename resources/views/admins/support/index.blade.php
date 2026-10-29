@extends('layouts.admin')
@php
    $seo = ['title' => 'Podpora · Administrácia', 'noindex' => true];
@endphp

@section('content')
    <x-pages.admin>

        <x-slot name="title">Podpora</x-slot>
        <x-slot name="lead">Správy používateľov z formulára „Napíšte nám" a z nástenky.</x-slot>

        <x-slot name="page">
            @php
                $num = fn ($value) => number_format((int) $value, 0, ',', ' ');
                $current = request('status');
                $tabUrl = fn (?string $status) => route('admin.support.index', array_filter(['status' => $status, 'search' => request('search')]));
            @endphp

            <div class="mb-5 flex flex-wrap items-center gap-2 text-sm">
                <a href="{{ $tabUrl(null) }}" @class(['ar-tab', 'ar-tab--on' => ! $current])>
                    Všetky · {{ $num($counts->sum()) }}
                </a>
                @foreach (\App\Enums\SupportStatus::cases() as $status)
                    <a href="{{ $tabUrl($status->value) }}" @class(['ar-tab', 'ar-tab--on' => $current === $status->value])>
                        {{ $status->label() }} · {{ $num($counts[$status->value] ?? 0) }}
                    </a>
                @endforeach

                <form method="GET" action="{{ route('admin.support.index') }}" class="ml-auto flex gap-2">
                    @if ($current)
                        <input type="hidden" name="status" value="{{ $current }}">
                    @endif
                    <label class="sr-only" for="support-search">Hľadať</label>
                    <input id="support-search" type="search" name="search" value="{{ request('search') }}"
                           placeholder="Predmet, text, e-mail" class="ar-field">
                    <button type="submit" class="ar-btn ar-btn--quiet">Hľadať</button>
                </form>
            </div>

            <x-dashboard.panel title="Vlákna" flush>
                <x-slot name="note">{{ $num($tickets->total()) }} vo výbere</x-slot>

                @forelse ($tickets as $ticket)
                    <article class="ar-item">
                        <div class="ar-item__body">
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                <x-dashboard.status-badge :label="$ticket->status->label()" :tone="$ticket->status->tone()" />
                                @if ($ticket->staff_seen_at === null)
                                    <x-dashboard.status-badge label="Neprečítané" tone="red" />
                                @endif
                                <span class="text-xs text-[color:var(--ar-muted)]">
                                    {{ $ticket->reference() }}
                                </span>
                            </div>
                            <a href="{{ route('admin.support.show', $ticket) }}" class="ar-item__title mt-1 block">{{ $ticket->subject }}</a>
                            @if ($ticket->latestMessage)
                                <p class="mt-1 text-sm text-[color:var(--ar-ink-soft)] break-words">
                                    {{ $ticket->latestMessage->is_staff ? 'Podpora: ' : '' }}{{ \Illuminate\Support\Str::limit($ticket->latestMessage->body, 140) }}
                                </p>
                            @endif
                            <div class="ar-item__meta">
                                <span><i class="ph ph-user"></i>{{ $ticket->user?->adminName() ?? '—' }}</span>
                                <span><i class="ph ph-chats"></i>{{ $ticket->messages_count }}</span>
                                <span title="{{ $ticket->last_activity_at?->format('j. n. Y H:i') }}">
                                    <i class="ph ph-clock"></i>{{ $ticket->last_activity_at?->diffForHumans() }}
                                </span>
                            </div>
                        </div>
                    </article>
                @empty
                    <x-dashboard.empty>Výberu nezodpovedá žiadne vlákno.</x-dashboard.empty>
                @endforelse
            </x-dashboard.panel>

            @if ($tickets->hasPages())
                <div class="mt-8">{{ $tickets->links() }}</div>
            @endif
        </x-slot>
    </x-pages.admin>
@endsection

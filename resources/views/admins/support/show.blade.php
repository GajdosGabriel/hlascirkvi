@extends('layouts.admin')
@php
    $seo = ['title' => $ticket->subject . ' · Podpora · Administrácia', 'noindex' => true];
@endphp

@section('content')
    <x-pages.admin>

        <x-slot name="title">{{ $ticket->subject }}</x-slot>
        <x-slot name="title_right">
            <a href="{{ route('admin.support.index') }}" class="ar-tab">
                <i class="ph ph-arrow-left mr-1" aria-hidden="true"></i>Podpora
            </a>
        </x-slot>

        <x-slot name="page">
            <div class="grid gap-6 xl:grid-cols-[1fr_18rem]">
                <div class="min-w-0">
                    <x-dashboard.panel class="mb-6">
                        @include('support._thread', ['staffView' => true])
                    </x-dashboard.panel>

                    <x-dashboard.panel title="Odpovedať v mene podpory">
                        <form method="POST" action="{{ route('admin.support.reply', $ticket) }}" class="space-y-3">
                            @csrf
                            <label class="sr-only" for="support-reply">Odpoveď</label>
                            <textarea id="support-reply" name="body" rows="5" maxlength="5000" required class="ar-field"
                                      @error('body') aria-invalid="true" @enderror>{{ old('body') }}</textarea>
                            @error('body')<p class="ar-error">{{ $message }}</p>@enderror
                            <p class="ar-hint">Používateľ dostane odpoveď e-mailom a uvidí ju v nástenke. Podpíše sa ako „Podpora HlasCirkvi.sk".</p>
                            <button type="submit" class="ar-btn ar-btn--accent">
                                <i class="ph ph-paper-plane-tilt"></i> Odoslať odpoveď
                            </button>
                        </form>
                    </x-dashboard.panel>
                </div>

                <aside class="space-y-6">
                    <x-dashboard.panel title="Vlákno">
                        <dl class="grid grid-cols-[auto_1fr] gap-x-3 gap-y-1 text-sm">
                            <dt class="text-gray-500">Číslo</dt>
                            <dd>{{ $ticket->reference() }}</dd>
                            <dt class="text-gray-500">Stav</dt>
                            <dd><x-dashboard.status-badge :label="$ticket->status->label()" :tone="$ticket->status->tone()" /></dd>
                            <dt class="text-gray-500">Téma</dt>
                            <dd>{{ $ticket->category->label() }}</dd>
                            <dt class="text-gray-500">Založené</dt>
                            <dd>{{ $ticket->created_at?->format('j. n. Y H:i') }}</dd>
                            @if ($ticket->page_url)
                                <dt class="text-gray-500">Stránka</dt>
                                <dd class="break-all"><a href="{{ $ticket->page_url }}" class="underline" target="_blank" rel="noopener">{{ $ticket->page_url }}</a></dd>
                            @endif
                            @if ($ticket->user_agent)
                                <dt class="text-gray-500">Prehliadač</dt>
                                <dd class="break-words text-xs text-gray-600">{{ $ticket->user_agent }}</dd>
                            @endif
                        </dl>

                        <x-slot name="footer">
                            <form method="POST" action="{{ route('admin.support.status', $ticket) }}" class="flex flex-wrap gap-2">
                                @csrf
                                @method('PUT')
                                @if ($ticket->status === \App\Enums\SupportStatus::Closed)
                                    <button type="submit" name="status" value="open" class="ar-btn ar-btn--quiet">Znovu otvoriť</button>
                                @else
                                    <button type="submit" name="status" value="closed" class="ar-btn ar-btn--quiet">
                                        <i class="ph ph-check"></i> Uzavrieť
                                    </button>
                                @endif
                            </form>
                        </x-slot>
                    </x-dashboard.panel>

                    <x-dashboard.panel title="Používateľ">
                        @if ($ticket->user)
                            <p class="text-sm font-semibold">{{ $ticket->user->adminName() }}</p>
                            <p class="text-sm break-all"><a href="mailto:{{ $ticket->user->email }}" class="underline">{{ $ticket->user->email }}</a></p>
                            <p class="mt-2 text-sm">
                                <a href="{{ route('admin.user.show', $ticket->user_id) }}" class="ar-link">Profil v administrácii</a>
                            </p>
                        @else
                            <p class="text-sm text-gray-500">Účet bol zmazaný.</p>
                        @endif

                        @if ($otherTickets->isNotEmpty())
                            <p class="ar-kicker mt-4 mb-2">Ďalšie vlákna</p>
                            <ul class="space-y-1 text-sm">
                                @foreach ($otherTickets as $other)
                                    <li>
                                        <a href="{{ route('admin.support.show', $other) }}" class="ar-link">{{ $other->reference() }} {{ \Illuminate\Support\Str::limit($other->subject, 40) }}</a>
                                        <span class="text-xs text-gray-500">· {{ $other->status->label() }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </x-dashboard.panel>
                </aside>
            </div>
        </x-slot>
    </x-pages.admin>
@endsection

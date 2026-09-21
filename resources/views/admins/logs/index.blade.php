@extends('layouts.admin')
@section('title')
    <title>{{ 'Admin denník udalostí.' }}</title>
@endsection

@section('content')
    <x-pages.admin>

        <x-slot name="title">Denník udalostí</x-slot>

        <x-slot name="page">
            @php
                $num = fn ($value) => number_format((int) $value, 0, ',', ' ');
                $tones = ['info' => 'gray', 'warning' => 'amber', 'error' => 'red'];
                $statusLabels = ['sent' => ['Odoslané', 'green'], 'failed' => ['Zlyhané', 'red'], 'skipped' => ['Preskočené', 'amber'], 'ok' => ['OK', 'green']];
                $filterUrl = fn (array $params) => route('admin.logs.index', array_filter($params + request()->except('page')));
            @endphp

            <div class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-5">
                <x-dashboard.metric label="Odoslané maily" :value="$num($summary->sent_day)">za 24 h · {{ $num($summary->sent_week) }} za 7 dní</x-dashboard.metric>
                <x-dashboard.metric label="Neodoslané maily" :value="$num($summary->failed_day)">za 24 h · {{ $num($summary->failed_week) }} za 7 dní</x-dashboard.metric>
                <x-dashboard.metric label="Chyby" :value="$num($summary->errors_day)">za 24 h · {{ $num($summary->errors_week) }} za 7 dní</x-dashboard.metric>
                <x-dashboard.metric label="Prihlásenia" :value="$num($summary->logins_day)">za 24 h</x-dashboard.metric>
                <x-dashboard.metric label="Neúspešné prihlásenia" :value="$num($summary->auth_failed_day)">za 24 h</x-dashboard.metric>
            </div>

            <form method="GET" action="{{ route('admin.logs.index') }}" class="mb-5 flex flex-wrap items-end gap-3 text-sm">
                @foreach (request()->except(['from', 'to', 'page']) as $name => $value)
                    @if (is_string($value))
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @endif
                @endforeach
                <label class="flex flex-col gap-1 text-gray-500">
                    Od
                    <input type="date" name="from" value="{{ request('from') }}" class="rounded-md border border-gray-300 px-2 py-1 text-gray-900">
                </label>
                <label class="flex flex-col gap-1 text-gray-500">
                    Do
                    <input type="date" name="to" value="{{ request('to') }}" class="rounded-md border border-gray-300 px-2 py-1 text-gray-900">
                </label>
                <button type="submit" class="ar-tab">Použiť</button>
                @if (request('recipient'))
                    <a href="{{ $filterUrl(['recipient' => null]) }}" class="ar-tab ar-tab--on" title="Zrušiť filter príjemcu">
                        {{ request('recipient') }} ✕
                    </a>
                @endif
                @if (request('user'))
                    <a href="{{ $filterUrl(['user' => null]) }}" class="ar-tab ar-tab--on" title="Zrušiť filter používateľa">
                        používateľ #{{ request('user') }} ✕
                    </a>
                @endif
            </form>

            <x-dashboard.panel title="Udalosti" flush>
                <x-slot name="note">
                    {{ $num($logs->total()) }} vo výbere · info sa drží {{ config('logging.system_log.days') }} dní, chyby {{ config('logging.system_log.error_days') }}
                </x-slot>

                @forelse ($logs as $log)
                    @php
                        [$statusLabel, $statusTone] = $statusLabels[$log->status] ?? [null, 'gray'];
                        $userName = $log->user ? trim($log->user->first_name . ' ' . $log->user->last_name) : null;
                    @endphp

                    <article class="ar-item">
                        <div class="ar-item__body">
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                <x-dashboard.status-badge :label="$log->event" :tone="$tones[$log->level] ?? 'gray'" :title="'Závažnosť: ' . $log->level" />
                                @if ($statusLabel)
                                    <x-dashboard.status-badge :label="$statusLabel" :tone="$statusTone" />
                                @endif
                                <span class="text-xs text-gray-400" title="{{ $log->created_at?->format('j. n. Y H:i:s') }}">
                                    {{ $log->created_at?->format('j. n. H:i') }} · {{ $log->created_at?->diffForHumans() }}
                                </span>
                            </div>

                            <p class="mt-1 text-sm text-[color:var(--ar-ink)] break-words">{{ $log->message }}</p>

                            <div class="ar-item__meta">
                                @if ($log->recipient)
                                    <a href="{{ $filterUrl(['recipient' => $log->recipient]) }}" class="hover:text-[color:var(--ar-accent)] break-all" title="Všetky udalosti tohto príjemcu">
                                        <i class="far fa-envelope"></i>{{ $log->recipient }}
                                    </a>
                                @endif
                                @if ($log->user_id)
                                    <a href="{{ route('admin.user.edit', $log->user_id) }}" class="hover:text-[color:var(--ar-accent)]">
                                        <i class="far fa-user"></i>{{ $userName ?: '#' . $log->user_id }}
                                    </a>
                                @endif
                                @if ($log->subject_type)
                                    <span><i class="fas fa-link"></i>{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}</span>
                                @endif
                                @if ($log->ip)
                                    <span><i class="fas fa-network-wired"></i>{{ $log->ip }}</span>
                                @endif
                            </div>

                            @if ($log->context)
                                <details class="mt-2 text-xs">
                                    <summary class="cursor-pointer text-gray-500 hover:text-gray-900">Podrobnosti</summary>
                                    <pre class="mt-1 max-h-72 overflow-auto whitespace-pre-wrap break-all rounded-md bg-gray-50 p-2 text-gray-700">{{ json_encode($log->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                </details>
                            @endif
                        </div>
                    </article>
                @empty
                    <x-dashboard.empty>Žiadne udalosti.</x-dashboard.empty>
                @endforelse
            </x-dashboard.panel>

            <div class="md:block flex justify-center my-8">
                {{ $logs->links() }}
            </div>

        </x-slot>
    </x-pages.admin>
@endsection

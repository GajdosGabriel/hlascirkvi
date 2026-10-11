@extends('layouts.admin')
@section('title')
    <title>{{ 'Admin denník udalostí – e-mail.' }}</title>
@endsection

@section('content')
    <x-pages.admin>

        <x-slot name="title">Odoslaný e-mail</x-slot>
        <x-slot name="title_right">
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('admin.logs.index') }}" class="ar-tab">
                <i class="ph ph-arrow-left mr-1" aria-hidden="true"></i>Denník
            </a>
        </x-slot>

        <x-slot name="page">
            @php
                $context = $log->context ?? [];
                $statusLabels = ['sent' => ['Odoslané', 'green'], 'failed' => ['Zlyhané', 'red'], 'skipped' => ['Preskočené', 'amber'], 'ok' => ['OK', 'green']];
                [$statusLabel, $statusTone] = $statusLabels[$log->status] ?? [$log->status, 'gray'];
                $fields = array_filter([
                    'Predmet' => $log->message,
                    'Od' => $context['from'] ?? null,
                    'Komu' => $log->recipient,
                    'Kópia' => $context['cc'] ?? null,
                    'Skrytá kópia' => $context['bcc'] ?? null,
                    'Odpoveď na' => $context['reply_to'] ?? null,
                    'Prílohy' => implode(', ', (array) ($context['attachments'] ?? [])),
                    'Čas' => $log->created_at?->format('j. n. Y H:i:s'),
                    'Chyba' => $context['error'] ?? null,
                ]);
                $omittedNotes = [
                    'sensitive' => 'Obsah tohto e-mailu sa neukladá, lebo obsahuje odkaz na zmenu hesla alebo potvrdzovací odkaz.',
                    'bulk' => 'Obsah newslettera sa do denníka neukladá.',
                ];
            @endphp

            <x-dashboard.panel :title="$template">
                <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-1 text-sm">
                    @foreach ($fields as $label => $value)
                        <dt class="text-gray-500">{{ $label }}</dt>
                        <dd class="break-words text-[color:var(--ar-ink)]">{{ $value }}</dd>
                    @endforeach
                    @if ($statusLabel)
                        <dt class="text-gray-500">Stav</dt>
                        <dd><x-dashboard.status-badge :label="$statusLabel" :tone="$statusTone" /></dd>
                    @endif
                </dl>

                @if ($log->body)
                    {{-- Odkazy v náhľade otvárame v novom okne, skripty sandbox nepustí. --}}
                    <iframe srcdoc="{{ '<base target="_blank">' . $log->body }}" sandbox="allow-popups allow-popups-to-escape-sandbox"
                        title="Obsah e-mailu" class="mt-4 h-[70vh] w-full rounded-md border border-gray-200 bg-white"></iframe>
                @else
                    <p class="mt-4 rounded-md bg-gray-50 p-3 text-sm text-gray-600">
                        {{ $omittedNotes[$context['body_omitted'] ?? ''] ?? 'Obsah tohto e-mailu nie je uložený (starší záznam alebo e-mail zlyhal ešte pred vykreslením).' }}
                    </p>
                @endif
            </x-dashboard.panel>

        </x-slot>
    </x-pages.admin>
@endsection

@extends('layouts.admin')

@section('title')
    <title>Administrácia</title>
@endsection

@section('content')
    <x-pages.admin>
        <x-slot name="title">Administrácia</x-slot>
        <x-slot name="page">
            @php
                $num = fn ($value) => number_format((int) $value, 0, ',', ' ');
                $compact = fn ($value) => (int) $value >= 1000000
                    ? number_format((int) $value / 1000000, 1, ',', ' ') . ' mil.'
                    : $num($value);
            @endphp

            <p class="mb-6 text-sm text-[color:var(--ar-ink-soft)]">Súhrnná správa obsahu, kanálov a používateľov Hlasu Cirkvi.</p>
            <div class="mb-6">
                <p class="ar-kicker mb-3">Celý web · {{ $now->format('j. n. Y') }}</p>
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <x-dashboard.metric label="Používatelia" :value="$num($users->total)">+{{ $num($users->new) }} za 30 dní</x-dashboard.metric>
                    <x-dashboard.metric label="Kanály" :value="$num($canals->total)">{{ $num($canals->published) }} zverejnených</x-dashboard.metric>
                    <x-dashboard.metric label="Články" :value="$num($posts->total)">{{ $num($posts->published) }} zverejnených</x-dashboard.metric>
                    <x-dashboard.metric label="Čakajú v bufferi" :value="$num($posts->waiting)">{{ $num($posts->published_today) }} publikovaných dnes</x-dashboard.metric>
                    <x-dashboard.metric label="Zhliadnutia celkovo" :value="$compact($posts->views)">Všetky aktívne články</x-dashboard.metric>
                    <x-dashboard.metric label="Komentáre" :value="$num($comments->total)">+{{ $num($comments->new) }} za 30 dní</x-dashboard.metric>
                    <x-dashboard.metric label="Otvorené modlitby" :value="$num($prayers->open)">{{ $num($prayers->fulfilled) }} vypočutých</x-dashboard.metric>
                </div>
            </div>

            <p class="ar-kicker mb-3">Rýchla správa</p>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ((new \App\View\Components\Navigation\AsideMenu)->adminMenu() as $item)
                    @continue($item['url'] === route('admin.home.index'))
                    <a href="{{ $item['url'] }}" class="ar-admin-shortcut ar-card ar-link flex items-center gap-3 rounded-xl p-4">
                        <span class="flex h-5 w-5 shrink-0 items-center justify-center text-[color:var(--ar-accent)]">
                            @include('components.icons.' . $item['icon'])
                        </span>
                        <span class="ar-display font-semibold">{{ trim($item['name']) }}</span>
                    </a>
                @endforeach
            </div>
            <style>
                .ar-admin-shortcut svg {
                    display: block;
                    width: 1.25rem !important;
                    height: 1.25rem !important;
                    margin: 0 !important;
                }
            </style>
        </x-slot>
    </x-pages.admin>
@endsection

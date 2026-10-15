@extends('layouts.admin')

@php
    $seo = ['title' => 'Oznamy · Administrácia', 'noindex' => true];
@endphp

@section('content')
    <x-pages.admin>

        <x-slot name="title">
            Oznamy
        </x-slot>

        <x-slot name="title_right">
            <a class="ar-btn ar-btn--accent" href="{{ route('admin.announcement.create') }}">
                <i class="ph ph-plus" aria-hidden="true"></i> Nový oznam
            </a>
        </x-slot>

        <x-slot name="page">

            {{-- Filter podľa miesta. Oznamov býva málo, preto stačia odkazy
                 namiesto formulára. --}}
            <div class="mb-5 flex flex-wrap gap-2">
                <a href="{{ route('admin.announcement.index') }}"
                   class="ar-tab {{ $placement ? '' : 'ar-tab--on' }}">Všetky</a>

                @foreach ($placements as $option)
                    <a href="{{ route('admin.announcement.index', ['placement' => $option->value]) }}"
                       title="{{ $option->description() }}"
                       class="ar-tab {{ $placement === $option ? 'ar-tab--on' : '' }}">{{ $option->label() }}</a>
                @endforeach
            </div>

            <x-dashboard.table label="Oznamy na webe">
                <thead>
                    <tr>
                        <th>Oznam</th>
                        <th>Umiestnenie</th>
                        <th>Stav</th>
                        <th>Zobrazovanie</th>
                        <th>Poradie</th>
                        <th>Akcia</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($announcements as $announcement)
                        <tr>
                            <td>
                                <div class="font-semibold">{{ $announcement->title }}</div>
                                @if ($announcement->body)
                                    <div class="text-gray-500">{{ \Illuminate\Support\Str::limit($announcement->body, 90) }}</div>
                                @endif
                                @if ($announcement->link_url)
                                    <div class="text-gray-500">
                                        <i class="ph ph-link" aria-hidden="true"></i> {{ $announcement->link_url }}
                                    </div>
                                @endif
                            </td>

                            <td>{{ $announcement->placement->label() }}</td>

                            <td>
                                {{-- Zapnutie a vypnutie priamo z výpisu; text oznamu ostáva uložený. --}}
                                <form method="POST" action="{{ route('admin.announcement.toggle', $announcement) }}">
                                    @csrf
                                    @method('PUT')
                                    {{-- Klikateľný stavový štítok v rovnakom vzhľade ako inde v administrácii. --}}
                                    <button type="submit" class="cursor-pointer"
                                            title="{{ $announcement->active ? 'Vypnúť zobrazovanie — text ostane uložený' : 'Zapnúť zobrazovanie' }}">
                                        <x-dashboard.status-badge :label="$announcement->active ? 'zapnutý' : 'vypnutý'" :tone="$announcement->active ? 'green' : 'gray'" />
                                    </button>
                                </form>

                                @if ($announcement->active && ! $announcement->isRunning())
                                    <div class="mt-1 text-xs text-[color:var(--ar-muted)]">mimo termínu</div>
                                @endif
                            </td>

                            <td class="whitespace-nowrap text-xs text-[color:var(--ar-ink-soft)]">
                                <div>Od: {{ $announcement->published_from?->format('d.m.Y H:i') ?? 'hneď' }}</div>
                                <div>Do: {{ $announcement->published_until?->format('d.m.Y H:i') ?? 'bez konca' }}</div>
                            </td>

                            <td>{{ $announcement->sort_order }}</td>

                            <td class="whitespace-nowrap">
                                <a class="ar-act" href="{{ route('admin.announcement.edit', $announcement) }}">
                                    <i class="ph ph-pencil-simple-line" aria-hidden="true"></i> Upraviť
                                </a>

                                <form method="POST" action="{{ route('admin.announcement.destroy', $announcement) }}"
                                      class="mt-1"
                                      data-confirm="Naozaj zmazať oznam „{{ $announcement->title }}“?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="ar-act ar-act--danger">
                                        <i class="ph ph-trash" aria-hidden="true"></i> Zmazať
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-dashboard.empty>Zatiaľ tu nie je žiadny oznam. <a class="ar-link" href="{{ route('admin.announcement.create') }}">Vytvoriť oznam</a></x-dashboard.empty>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-dashboard.table>

            @if ($announcements->hasPages())
                <div class="mt-8">{{ $announcements->links() }}</div>
            @endif

        </x-slot>
    </x-pages.admin>
@endsection

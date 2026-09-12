@extends('layouts.admin')

@section('title')
    <title>{{ 'Oznamy na webe' }}</title>
@endsection

@section('content')
    <x-pages.admin>

        <x-slot name="title">
            Oznamy
        </x-slot>

        <x-slot name="title_right">
            <a class="btn btn-primary" href="{{ route('admin.announcement.create') }}">
                <i class="fas fa-plus" aria-hidden="true"></i> Nový oznam
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
                                        <i class="fas fa-link" aria-hidden="true"></i> {{ $announcement->link_url }}
                                    </div>
                                @endif
                            </td>

                            <td>{{ $announcement->placement->label() }}</td>

                            <td>
                                {{-- Zapnutie a vypnutie priamo z výpisu; text oznamu ostáva uložený. --}}
                                <form method="POST" action="{{ route('admin.announcement.toggle', $announcement) }}">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit"
                                            class="whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold
                                                {{ $announcement->active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}"
                                            title="{{ $announcement->active ? 'Vypnúť zobrazovanie — text ostane uložený' : 'Zapnúť zobrazovanie' }}">
                                        {{ $announcement->active ? 'zapnutý' : 'vypnutý' }}
                                    </button>
                                </form>

                                @if ($announcement->active && ! $announcement->isRunning())
                                    <div class="mt-1 text-xs text-gray-500">mimo termínu</div>
                                @endif
                            </td>

                            <td class="whitespace-nowrap text-xs text-gray-600">
                                <div>Od: {{ $announcement->published_from?->format('d.m.Y H:i') ?? 'hneď' }}</div>
                                <div>Do: {{ $announcement->published_until?->format('d.m.Y H:i') ?? 'bez konca' }}</div>
                            </td>

                            <td>{{ $announcement->sort_order }}</td>

                            <td class="whitespace-nowrap">
                                <a class="btn btn-default" href="{{ route('admin.announcement.edit', $announcement) }}">
                                    <i class="fas fa-edit" aria-hidden="true"></i> Upraviť
                                </a>

                                <form method="POST" action="{{ route('admin.announcement.destroy', $announcement) }}"
                                      class="mt-2"
                                      onsubmit="return confirm('Naozaj zmazať oznam „{{ $announcement->title }}“?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-default text-red-700">
                                        <i class="fas fa-trash" aria-hidden="true"></i> Zmazať
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-dashboard.empty>Zatiaľ tu nie je žiadny oznam</x-dashboard.empty>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-dashboard.table>

            <div class="my-8 flex justify-center md:block">
                {{ $announcements->links() }}
            </div>

        </x-slot>
    </x-pages.admin>
@endsection

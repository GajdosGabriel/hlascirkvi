@extends('layouts.admin')

@php
    $seo = ['title' => 'Osoby · Administrácia', 'noindex' => true];
    $filters = \App\Http\Controllers\Admin\PersonController::FILTERS;
    $query = fn (array $changes) => array_filter(array_merge(request()->only(['q', 'filter', 'poradie']), $changes, ['page' => null]), fn ($v) => $v !== null && $v !== '');
@endphp

@section('content')
    <x-pages.admin>

        <x-slot name="title">Osoby</x-slot>

        <x-slot name="title_right">
            <a class="ar-btn ar-btn--accent" href="{{ route('admin.persons.create') }}">
                <i class="ph ph-plus" aria-hidden="true"></i> Nová osoba
            </a>
        </x-slot>

        <x-slot name="page">
            <p class="mb-4 text-sm text-(--ar-ink-soft)">
                Kazatelia, kňazi a hostia, ktorí vystupujú vo videách. Väčšinu rozpoznali pravidlá z názvov a popisov;
                <strong>„Na kontrolu (AI)“</strong> sú osoby, ktoré založila AI pri nových videách.
            </p>

            <div class="mb-5 flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.persons.index', $query(['filter' => null])) }}"
                   class="ar-tab {{ $filter ? '' : 'ar-tab--on' }}">Všetky</a>
                @foreach ($filters as $key => $label)
                    <a href="{{ route('admin.persons.index', $query(['filter' => $key])) }}"
                       class="ar-tab {{ $filter === $key ? 'ar-tab--on' : '' }}">
                        {{ $label }}@if ($key === 'ai' && $aiCount) <span class="ml-1 font-bold">{{ $aiCount }}</span>@endif
                    </a>
                @endforeach

                <form method="GET" action="{{ route('admin.persons.index') }}" class="ar-search ar-search--open ml-auto">
                    @if ($filter)<input type="hidden" name="filter" value="{{ $filter }}">@endif
                    <label for="person-search" class="sr-only">Hľadať osobu</label>
                    <input id="person-search" type="search" name="q" value="{{ $search }}" placeholder="Hľadať meno…">
                    <button type="submit" title="Hľadať"><i class="ph ph-magnifying-glass" aria-hidden="true"></i></button>
                </form>
            </div>

            <x-dashboard.table label="Osoby">
                <thead>
                    <tr>
                        <th>Osoba</th>
                        <th>
                            <a class="ar-link" href="{{ route('admin.persons.index', $query(['poradie' => null])) }}">Videá</a>
                        </th>
                        <th>Kanály</th>
                        <th>Vlastný kanál</th>
                        <th>
                            <a class="ar-link" href="{{ route('admin.persons.index', $query(['poradie' => 'nove'])) }}">Stav</a>
                        </th>
                        <th>Akcia</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($persons as $person)
                        <tr>
                            <td>
                                <a class="font-semibold ar-link" href="{{ route('admin.persons.edit', $person) }}">{{ $person->name }}</a>
                                <div class="text-xs text-(--ar-muted)">/osoba/{{ $person->slug }}</div>
                            </td>
                            <td>{{ number_format($person->posts_count, 0, ',', ' ') }}</td>
                            <td>{{ $person->canals_count ?: '—' }}</td>
                            <td>{{ $person->canal?->title ?? '—' }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.persons.verify', $person) }}">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="cursor-pointer"
                                            title="{{ $person->verified_at ? 'Zrušiť overenie' : 'Označiť ako skontrolovanú' }}">
                                        <x-dashboard.status-badge :label="$person->verified_at ? 'overená' : 'neoverená'" :tone="$person->verified_at ? 'green' : 'gray'" />
                                    </button>
                                </form>
                            </td>
                            <td class="whitespace-nowrap">
                                <a class="ar-act" href="{{ route('admin.persons.edit', $person) }}">
                                    <i class="ph ph-pencil-simple-line" aria-hidden="true"></i> Upraviť
                                </a>
                                <a class="ar-act" href="{{ route('person.show', $person) }}" target="_blank" rel="noopener">
                                    <i class="ph ph-arrow-square-out" aria-hidden="true"></i> Na webe
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6"><x-dashboard.empty>Pre tento výber sme nenašli žiadnu osobu.</x-dashboard.empty></td>
                        </tr>
                    @endforelse
                </tbody>
            </x-dashboard.table>

            <div class="mt-5">{{ $persons->onEachSide(1)->links() }}</div>
        </x-slot>
    </x-pages.admin>
@endsection

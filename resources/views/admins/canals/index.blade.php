@extends('layouts.admin')

@section('title')
    <title>{{ 'Všetky organizácie' }}</title>
@endsection

@section('content')
    <x-pages.admin>

        <x-slot name="title">
            Kanály
        </x-slot>

        <x-slot name="title_right">
            <new-canal />
        </x-slot>


        <x-slot name="page">
            @php
                $num = fn ($value) => number_format((int) $value, 0, ',', ' ');

                // Dlaždica prepína svoj filter: klik na zapnutú ho vypne.
                // Radenie a hľadanie ostávajú, stránkovanie sa zahodí.
                $toggle = fn (string $key) => route('admin.canal.index', (request()->has($key) || ($key === 'unpublished' && request('publication') === 'unpublished'))
                    ? request()->except($key === 'unpublished' ? [$key, 'page', 'publication'] : [$key, 'page'])
                    : array_merge(request()->except($key === 'unpublished' ? ['page', 'month', 'publication'] : ['page', 'month']), [$key => 1]));

                $tiles = [
                    ['key' => null, 'label' => 'Kanálov spolu', 'value' => $summary->total, 'note' => 'bez zrušených'],
                    ['key' => 'fresh', 'label' => 'Nové', 'value' => $summary->fresh, 'note' => 'za ' . \App\Filters\CanalFilters::FRESH_DAYS . ' dní'],
                    ['key' => 'silent', 'label' => 'Utíchnuté', 'value' => $summary->silent, 'note' => 'bez príspevku ' . \App\Filters\CanalFilters::SILENT_DAYS . ' dní'],
                    ['key' => 'orphans', 'label' => 'Bez správcu', 'value' => $summary->orphans, 'note' => 'nemá ich kto spravovať'],
                    ['key' => 'youtubeOff', 'label' => 'YouTube vypnutý', 'value' => $summary->youtubeOff, 'note' => 'import sa zastavil'],
                    ['key' => 'unpublished', 'label' => 'Nepublikované', 'value' => $summary->unpublished, 'note' => 'skryté na webe'],
                ];

                $monthsMax = max(1, max(array_column($registrations, 'count')));
                $yearTotal = array_sum(array_column($registrations, 'count'));
                $activeMonth = request('month');
            @endphp

            {{-- Hľadanie a radenie. Zapnuté filtre dlaždíc sa prenášajú skrytými poľami. --}}
            <form method="GET" action="{{ route('admin.canal.index') }}"
                  class="ar-canal-search mb-5" role="search">
                <select name="publication" class="form-control ar-canal-search__sort"
                        aria-label="{{ __('canal.filter.label') }}" onchange="this.form.submit()">
                    @foreach (__('canal.filter.options') as $key => $label)
                        <option value="{{ $key }}" @selected(request('publication', request('deletedAt') ? 'deletedAt' : (request('unpublished') ? 'unpublished' : '')) === (string) $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="type" class="form-control ar-canal-search__sort"
                        aria-label="{{ __('canal.type.label') }}" onchange="this.form.submit()">
                    <option value="">{{ __('canal.type.all') }}</option>
                    @foreach (\App\Enums\CanalType::cases() as $type)
                        <option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ __('canal.type.options.' . $type->value) }}</option>
                    @endforeach
                </select>
                @foreach (request()->except(['search', 'sort', 'page', 'publication', 'unpublished', 'deletedAt', 'type']) as $key => $value)
                    @if (is_scalar($value))
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach

                <input type="search" name="search" value="{{ request('search') }}"
                       placeholder="Hľadať kanál podľa názvu"
                       class="form-control ar-canal-search__input"
                       aria-label="Hľadať kanál">

                <select name="sort" class="form-control ar-canal-search__sort" aria-label="Radenie"
                        onchange="this.form.submit()">
                    @foreach (\App\Filters\CanalFilters::SORTS as $value => $label)
                        <option value="{{ $value }}" @selected(request('sort', 'newest') === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <button type="submit" class="ar-btn ar-btn--quiet">Hľadať</button>

                <span class="ml-auto text-xs text-[color:var(--ar-ink-soft)]">
                    {{ $num($canals->total()) }} {{ $canals->total() === 1 ? 'kanál' : ($canals->total() >= 2 && $canals->total() <= 4 ? 'kanály' : 'kanálov') }} vo výbere

                </span>
            </form>

            {{-- Súhrn: každé číslo je zároveň filter výpisu. --}}
            <div class="ar-canal-tiles mb-6">
                @foreach ($tiles as $tile)
                    @php
                        $active = $tile['key'] ? (request()->has($tile['key']) || ($tile['key'] === 'unpublished' && request('publication') === 'unpublished')) : ! request()->hasAny(array_filter(array_column($tiles, 'key')));
                        $href = $tile['key'] ? $toggle($tile['key']) : route('admin.canal.index', request()->only(['search', 'sort']));
                    @endphp
                    <a href="{{ $href }}" @class(['ar-kpi', 'is-active' => $active])>
                        <span class="ar-kpi__label">{{ $tile['label'] }}</span>
                        <span class="ar-kpi__value">{{ $num($tile['value']) }}</span>
                        <span class="ar-kpi__note">{{ $tile['note'] }}</span>
                    </a>
                @endforeach
            </div>

            {{-- Registrácie za posledný rok. Stĺpec je odkaz na kanály z toho mesiaca. --}}
            <div class="ar-panel mb-6">
                <div class="ar-panel__body">
                    <div class="mb-2 flex items-baseline justify-between gap-4">
                        <span class="ar-panel__title">Nové kanály po mesiacoch</span>
                        <span class="ar-panel__note">
                            {{ $num($yearTotal) }} za rok
                            @if ($activeMonth)
                                · <a href="{{ route('admin.canal.index', request()->except(['month', 'page'])) }}" class="ar-link">zrušiť výber mesiaca</a>
                            @endif
                        </span>
                    </div>

                    <div class="ar-months">
                        @foreach ($registrations as $month)
                            <div>
                                <a href="{{ route('admin.canal.index', array_merge(request()->except(['page', 'fresh']), ['month' => $month['month']])) }}"
                                   @class([
                                       'ar-months__bar',
                                       'ar-months__bar--empty' => $month['count'] === 0,
                                       'is-active' => $activeMonth === $month['month'],
                                   ])
                                   style="height: {{ max(3, round($month['count'] / $monthsMax * 56)) }}px"
                                   title="{{ $month['label'] }} — {{ $month['count'] }}"
                                   aria-label="{{ $month['label'] }}: {{ $month['count'] }} nových kanálov"></a>
                                <span class="ar-months__label block font-semibold">{{ $month['count'] ?: "\u{00A0}" }}</span>
                                <span class="ar-months__label block" style="margin-top: 0">{{ \Illuminate\Support\Str::before($month['label'], ' ') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <x-canal.list :canals="$canals" :admin="true" />


            <div class="md:block flex justify-center my-8">
                {{ $canals->links() }}
            </div>

        </x-slot>

        </x-pages.admin>
    @endsection

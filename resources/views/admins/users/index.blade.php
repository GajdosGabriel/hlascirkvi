@extends('layouts.admin')
@section('title')
    <title>{{ 'Registrovaný užívatelia.' }}</title>
@endsection

@section('content')
    <x-pages.admin>

        <x-slot name="title">
            Registrovaný užívatelia
        </x-slot>

        <x-slot name="title_right">
            {{--  --}}
        </x-slot>



        <x-slot name="page">
            @php
                $num = fn ($value) => number_format((int) $value, 0, ',', ' ');
                $days = \App\Filters\UserFilters::RECENT_DAYS;
                $quickFilters = ['fresh', 'active', 'never', 'unverified', 'via', 'status'];

                // Dlaždica prepína svoj filter: klik na zapnutú ho vypne.
                // Hľadanie a radenie ostávajú, ostatné rýchle filtre a stránkovanie sa zahodia.
                $toggle = function (string $key, $value = 1) use ($quickFilters) {
                    $on = (string) request($key) === (string) $value;

                    return route('admin.user.index', $on
                        ? request()->except([$key, 'page'])
                        : array_merge(request()->except(array_merge($quickFilters, ['page'])), [$key => $value]));
                };

                // Popis k číslu ide do title — v páse ostáva len číslo a krátky názov.
                $tiles = [
                    ['key' => null, 'label' => 'spolu', 'value' => $summary->total, 'note' => 'Všetky účty bez zrušených'],
                    ['key' => 'fresh', 'label' => 'noví', 'value' => $summary->fresh, 'note' => "Registrácia za posledných $days dní"],
                    ['key' => 'active', 'label' => 'aktívni', 'value' => $summary->active, 'note' => "Prihlásení za posledných $days dní"],
                    ['key' => 'never', 'label' => 'nikdy neprihlásení', 'value' => $summary->never, 'note' => 'Bez záznamu o prihlásení (sledujeme ho až od zavedenia)'],
                    ['key' => 'unverified', 'label' => 'neoverený e-mail', 'value' => $summary->unverified, 'note' => 'Nepotvrdili odkaz v e-maile'],
                    ['key' => 'status', 'filter' => \App\Enums\ModelStatus::PendingReview->value, 'label' => 'čaká na schválenie', 'value' => $summary->pending, 'note' => 'Stav účtu'],
                    ['key' => 'status', 'filter' => \App\Enums\ModelStatus::Blocked->value, 'label' => 'zablokovaní', 'value' => $summary->blocked, 'note' => 'Stav účtu'],
                ];

                $viaTotal = max(1, array_sum($summary->via));
            @endphp

            {{-- Súhrn v jednom páse: každé číslo je zároveň filter výpisu, klik na zapnuté ho vypne. --}}
            <nav class="ar-stats mb-4" aria-label="Súhrn používateľov">
                @foreach ($tiles as $tile)
                    @php
                        $filter = $tile['filter'] ?? 1;
                        $active = $tile['key']
                            ? (string) request($tile['key']) === (string) $filter
                            : ! request()->hasAny($quickFilters);
                        $href = $tile['key'] ? $toggle($tile['key'], $filter) : route('admin.user.index', request()->only(['search', 'sort']));
                    @endphp
                    <a href="{{ $href }}" title="{{ $tile['note'] }}"
                       @class(['ar-stats__item', 'is-active' => $active]) @if ($active) aria-current="true" @endif>
                        <strong>{{ $num($tile['value']) }}</strong> {{ $tile['label'] }}
                    </a>
                @endforeach

                {{-- Spôsob posledného prihlásenia — podiel z tých, čo sa už prihlásili. --}}
                @if ($summary->via)
                    <span class="ar-stats__sep" aria-hidden="true"></span>
                    <span class="ar-stats__caption">Prihlásenie cez</span>
                    @foreach ($summary->via as $via => $count)
                        <a href="{{ $toggle('via', $via) }}" title="{{ round($count / $viaTotal * 100) }} % prihlásených"
                           @class(['ar-stats__item', 'is-active' => request('via') === $via]) @if (request('via') === $via) aria-current="true" @endif>
                            <strong>{{ $num($count) }}</strong> {{ \App\Models\User::loginViaLabel($via) }}
                        </a>
                    @endforeach
                @endif
            </nav>

            @include('users.users_table')

            <div class="md:block flex justify-center my-8">
                {{ $users->links() }}
            </div>

        </x-slot>
        </x-pages.admin>
    @endsection

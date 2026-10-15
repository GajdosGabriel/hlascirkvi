@extends('layouts.admin')

@php
    $seo = ['title' => 'Kanály na úvode · Administrácia', 'noindex' => true];
@endphp

@section('content')
    <x-pages.admin>

        <x-slot name="title">Kanály na úvode</x-slot>
        <x-slot name="lead">Kto môže byť na kartách „Kresťanské osobnosti“ a „Cirkvi a spoločenstvá“ na úvodnej stránke.</x-slot>

        <x-slot name="title_right">
            <a class="ar-btn ar-btn--quiet" href="{{ route('frontlist.index') }}" target="_blank" rel="noopener">
                <i class="ph ph-arrow-square-out" aria-hidden="true"></i> Pozrieť na webe
            </a>
        </x-slot>

        <x-slot name="page">

            {{-- Vysvetlenie je potrebné raz, potom zavadzia — preto zbalené a v drobnom.
                 Väčším písmom ako zvyšok administrácie tlačilo tabuľku pod okraj. --}}
            <x-dashboard.panel class="mb-5">
                <details class="text-sm text-[color:var(--ar-ink-soft)]">
                    <summary class="cursor-pointer font-semibold text-[color:var(--ar-ink)]">Ako sa karta radí</summary>
                <p class="mt-2">
                    Kartu „Kresťanské osobnosti" aj „Cirkvi a spoločenstvá" radí záujem návštevníkov, nie ručné
                    poradie. Skóre sú zhliadnutia príspevkov kanála a noví sledovatelia
                    (1 sledovateľ = {{ config('frontlist.follow_weight') }} zhliadnutí) za posledných
                    {{ config('frontlist.window_days') }} dní; každých {{ config('frontlist.half_life_days') }} dní
                    starý záujem stratí polovicu váhy.
                </p>
                <p class="mt-2">
                    Z {{ config('frontlist.card_limit') }} miest na karte {{ config('frontlist.discovery_slots') }}
                    dostanú kanály, ktoré za posledných {{ config('frontlist.discovery_days') }} dní niečo zverejnili —
                    každý deň iné. Tu v zozname rozhodujete, kto na kartu môže a na ktorú patrí.
                </p>
                </details>
            </x-dashboard.panel>

            <x-dashboard.panel title="Pridať kanál" class="mb-5">
                {{-- Hľadanie, nie rozbaľovací zoznam: kanálov je vyše päťsto. --}}
                <form method="GET" action="{{ route('admin.frontlist.index') }}" class="flex flex-wrap gap-2">
                    <span class="ar-searchbox w-full max-w-sm">
                        <input type="search" name="hladat" value="{{ $hladane }}" class="ar-field"
                               placeholder="Meno kanála alebo osobnosti…" autocomplete="off"
                               aria-label="Hľadať kanál">
                        <x-search-button label="Hľadať kanál" />
                    </span>
                </form>

                @if ($hladane !== '')
                    @forelse ($najdene as $canal)
                        <form method="POST" action="{{ route('admin.frontlist.store') }}"
                              class="mt-2 flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-2">
                            @csrf
                            <input type="hidden" name="canal" value="{{ $canal->id }}">

                            <span>
                                {{ $canal->title }}
                                @unless ($canal->published)
                                    {{-- Skrytý kanál sa do zoznamu pridať dá, ale na webe
                                         sa neukáže, kým ho niekto nezverejní. --}}
                                    <span class="ar-badge ar-badge--warn ml-1">skrytý kanál</span>
                                @endunless
                            </span>

                            <span class="flex items-center gap-2">
                                <select name="identity_mode" class="ar-field" aria-label="Režim identity" required>
                                    @foreach ($types as $type)
                                        <option value="{{ $type->value }}" @selected(($canal->identity_mode ?? \App\Enums\CanalIdentityMode::Personal) === $type)>{{ $type->label() }}</option>
                                    @endforeach
                                </select>
                                <button class="ar-btn ar-btn--accent"><i class="ph ph-plus" aria-hidden="true"></i> Pridať</button>
                            </span>
                        </form>
                    @empty
                        <p class="mt-3 text-[color:var(--ar-muted)]">Nič sa nenašlo — alebo je taký kanál v zozname už zaradený.</p>
                    @endforelse
                @endif
            </x-dashboard.panel>

            <x-dashboard.table label="Kanály v prednom zozname">
                <thead>
                    <tr>
                        <th>Kanál</th>
                        <th>Typ</th>
                        <th title="Záujem za posledných {{ config('frontlist.window_days') }} dní">Skóre</th>
                        <th>Zverejnených</th>
                        <th>Naposledy</th>
                        <th>{{ __('canal.actions.label') }}</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($canals as $canal)
                        <tr>
                            <td>
                                <a href="{{ route('organizations.show', [$canal->id]) }}" target="_blank" rel="noopener"
                                   class="font-semibold">{{ $canal->title }}</a>

                                @if (in_array($canal->id, $cardIds, true))
                                    <span class="ml-1 rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-800"
                                          title="Dnes je na karte v bočnom paneli">na karte</span>
                                @elseif ($canal->identity_mode)
                                    <span class="ml-1 text-xs text-[color:var(--ar-muted)]">len v celom zozname</span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap">
                                @if ($canal->identity_mode)
                                    {{ $canal->identity_mode->label() }}
                                @else
                                    <span class="text-red-700" title="Kanál bez typu na webe nevidno">bez typu</span>
                                @endif

                                {{-- Prepnutie typu jedným tlačidlom: typy sú len dva a pri
                                     migrácii ich určil odhad podľa názvu. --}}
                                <form method="POST" action="{{ route('admin.frontlist.type', $canal->id) }}" class="mt-1 flex gap-1">
                                    @csrf @method('PUT')
                                    @foreach ($types as $type)
                                        @continue($canal->identity_mode === $type)
                                        <button name="identity_mode" value="{{ $type->value }}" class="text-xs underline">
                                            → {{ $type->label() }}
                                        </button>
                                    @endforeach
                                </form>
                            </td>

                            <td>{{ number_format($canal->score, 1, ',', ' ') }}</td>

                            <td>{{ $canal->postsCount }}</td>

                            <td class="whitespace-nowrap">
                                @if ($canal->lastPostAt)
                                    <span @class(['text-red-700' => $canal->isStale()])>{{ $canal->lastPostAt->format('j. n. Y') }}</span>
                                @else
                                    <span class="text-red-700">nič zverejnené</span>
                                @endif

                                @if ($canal->isStale())
                                    {{-- Na titulke roky viseli kanály, z ktorých naposledy
                                         niečo vyšlo v roku 2017. --}}
                                    <div class="text-xs text-red-700">spiaci kanál</div>
                                @endif
                            </td>

                            <td>
                                @php($canalModel = $canalModels->get($canal->id))
                                @if ($canalModel)
                                    <dropdown-slot label="{{ __('canal.actions.label') }}">
                                        @can('view', $canalModel)
                                            <a href="{{ route('organizations.show', $canal->id) }}">
                                                <i class="ph ph-eye" aria-hidden="true"></i>
                                                {{ __('canal.actions.show') }}
                                            </a>
                                        @endcan
                                        @can('manage', $canalModel)
                                            <a href="{{ route('profile.canals.edit', $canal->id) }}">
                                                <i class="ph ph-pencil-simple" aria-hidden="true"></i>
                                                {{ __('canal.actions.edit') }}
                                            </a>
                                            <a href="{{ route('profile.canals.show', $canal->id) }}">
                                                <i class="ph ph-columns" aria-hidden="true"></i>
                                                {{ __('canal.actions.dashboard') }}
                                            </a>
                                        @endcan
                                        @can('superadmin')
                                            <hr class="ui-dropdown__divider">
                                            <form method="POST" action="{{ route('admin.frontlist.destroy', $canal->id) }}"
                                                  data-confirm="{{ __('canal.actions.remove_confirmation', ['title' => $canal->title]) }}">
                                                @csrf @method('DELETE')
                                                <button type="submit" title="{{ __('canal.actions.remove_hint') }}">
                                                    <i class="ph ph-x" aria-hidden="true"></i>
                                                    {{ __('canal.actions.remove') }}
                                                </button>
                                            </form>
                                        @endcan
                                    </dropdown-slot>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">Zoznam je prázdny.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-dashboard.table>

        </x-slot>
    </x-pages.admin>
@endsection

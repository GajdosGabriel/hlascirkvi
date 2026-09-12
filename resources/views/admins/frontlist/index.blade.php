@extends('layouts.admin')

@section('title')
    <title>{{ 'Predný zoznam kanálov' }}</title>
@endsection

@section('content')
    <x-pages.admin>

        <x-slot name="title">
            Predný zoznam
        </x-slot>

        <x-slot name="title_right">
            <a class="btn btn-primary" href="{{ route('frontlist.index') }}" target="_blank" rel="noopener">
                <i class="fas fa-external-link-alt" aria-hidden="true"></i> Pozrieť na webe
            </a>
        </x-slot>

        <x-slot name="page">

            <x-dashboard.panel title="Pridať kanál" class="mb-5">
                {{-- Hľadanie, nie rozbaľovací zoznam: kanálov je vyše päťsto. --}}
                <form method="GET" action="{{ route('admin.frontlist.index') }}" class="flex flex-wrap gap-2">
                    <input type="search" name="hladat" value="{{ $hladane }}" class="form-control max-w-sm"
                           placeholder="Meno kanála alebo osobnosti…" autocomplete="off">
                    <button class="btn btn-primary">Hľadať</button>
                </form>

                @if ($hladane !== '')
                    @forelse ($najdene as $canal)
                        <form method="POST" action="{{ route('admin.frontlist.store') }}"
                              class="mt-2 flex items-center justify-between gap-3 border-t border-gray-100 pt-2">
                            @csrf
                            <input type="hidden" name="canal" value="{{ $canal->id }}">

                            <span>
                                {{ $canal->title }}
                                @unless ($canal->published)
                                    {{-- Skrytý kanál sa do zoznamu pridať dá, ale na webe
                                         sa neukáže, kým ho niekto nezverejní. --}}
                                    <span class="ml-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-800">skrytý kanál</span>
                                @endunless
                            </span>

                            <button class="btn btn-primary">Pridať</button>
                        </form>
                    @empty
                        <p class="mt-3 text-gray-500">Nič sa nenašlo — alebo je taký kanál v zozname už zaradený.</p>
                    @endforelse
                @endif
            </x-dashboard.panel>

            <x-dashboard.table label="Kanály v prednom zozname">
                <thead>
                    <tr>
                        <th>Poradie</th>
                        <th>Kanál</th>
                        <th>Zverejnených</th>
                        <th>Naposledy</th>
                        <th>Akcia</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($canals as $index => $canal)
                        <tr>
                            <td class="whitespace-nowrap">
                                {{ $index + 1 }}.

                                {{-- Posun o jedno miesto. Ťahanie myšou by sem prinieslo
                                     skript, ktorý by bol v administrácii jediný svojho druhu. --}}
                                <form method="POST" action="{{ route('admin.frontlist.move', $canal->id) }}" class="inline">
                                    @csrf @method('PUT')
                                    <button name="smer" value="hore" class="px-1 disabled:opacity-25"
                                            title="O miesto vyššie" @disabled($index === 0)>
                                        <i class="fas fa-arrow-up" aria-hidden="true"></i>
                                    </button>
                                    <button name="smer" value="dole" class="px-1 disabled:opacity-25"
                                            title="O miesto nižšie" @disabled($index === $canals->count() - 1)>
                                        <i class="fas fa-arrow-down" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </td>

                            <td>
                                <a href="{{ route('organizations.show', [$canal->id]) }}" target="_blank" rel="noopener"
                                   class="font-semibold">{{ $canal->title }}</a>

                                @if ($index < config('frontlist.card_limit'))
                                    <span class="ml-1 rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-800"
                                          title="Vojde sa na kartu v bočnom paneli úvodnej stránky">na titulke</span>
                                @else
                                    <span class="ml-1 text-xs text-gray-500"
                                          title="Na titulke je vidieť len prvých {{ config('frontlist.card_limit') }}">len v celom zozname</span>
                                @endif
                            </td>

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
                                <form method="POST" action="{{ route('admin.frontlist.destroy', $canal->id) }}"
                                      onsubmit="return confirm('Vyradiť kanál {{ $canal->title }} z predného zoznamu?')">
                                    @csrf @method('DELETE')
                                    <button class="btn" title="Vyradiť zo zoznamu — kanál samotný ostáva">Vyradiť</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">Zoznam je prázdny.</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-dashboard.table>

        </x-slot>
    </x-pages.admin>
@endsection

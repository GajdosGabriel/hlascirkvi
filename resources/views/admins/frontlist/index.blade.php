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

            <x-dashboard.panel title="Ako sa karta radí" class="mb-5">
                <p>
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
            </x-dashboard.panel>

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
                              class="mt-2 flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-2">
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

                            <span class="flex items-center gap-2">
                                <select name="type" class="form-control" aria-label="Typ kanála" required>
                                    @foreach ($types as $type)
                                        <option value="{{ $type->value }}" @selected(($canal->type ?? \App\Enums\CanalType::Personal) === $type)>{{ $type->label() }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-primary">Pridať</button>
                            </span>
                        </form>
                    @empty
                        <p class="mt-3 text-gray-500">Nič sa nenašlo — alebo je taký kanál v zozname už zaradený.</p>
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
                        <th>Akcia</th>
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
                                @elseif ($canal->type)
                                    <span class="ml-1 text-xs text-gray-500">len v celom zozname</span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap">
                                @if ($canal->type)
                                    {{ $canal->type->label() }}
                                @else
                                    <span class="text-red-700" title="Kanál bez typu na webe nevidno">bez typu</span>
                                @endif

                                {{-- Prepnutie typu jedným tlačidlom: typy sú len dva a pri
                                     migrácii ich určil odhad podľa názvu. --}}
                                <form method="POST" action="{{ route('admin.frontlist.type', $canal->id) }}" class="mt-1 flex gap-1">
                                    @csrf @method('PUT')
                                    @foreach ($types as $type)
                                        @continue($canal->type === $type)
                                        <button name="type" value="{{ $type->value }}" class="text-xs underline">
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
                                <form method="POST" action="{{ route('admin.frontlist.destroy', $canal->id) }}"
                                      onsubmit="return confirm('Vyradiť kanál {{ $canal->title }} z predného zoznamu?')">
                                    @csrf @method('DELETE')
                                    <button class="btn" title="Vyradiť zo zoznamu — kanál samotný ostáva">Vyradiť</button>
                                </form>
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

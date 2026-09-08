{{-- Hlavička a rám správcovských stránok kanála. Popis v App\View\Components\Dashboard\Shell. --}}
@php
    $num = fn ($value) => number_format((int) $value, 0, ',', ' ');
    $tab = $current();
@endphp

<header class="border-b border-[color:var(--ar-line)] bg-white">
    <div class="mx-auto max-w-6xl px-4">

        <div class="py-3 text-sm text-gray-500">
            <a href="{{ url('/') }}" class="hover:text-gray-900">Hlas Cirkvi</a>
            <span class="mx-2 text-gray-300">/</span>

            @if ($section === 'dashboard')
                <span class="text-gray-700">Nástenka</span>
            @else
                <a href="{{ route('profile.dashboard') }}" class="hover:text-gray-900">Nástenka</a>
                <span class="mx-2 text-gray-300">/</span>
                <span class="text-gray-700">{{ $tab['label'] ?? $heading }}</span>
            @endif
        </div>

        <div class="flex flex-wrap items-start gap-4 border-t border-[color:var(--ar-line)] pt-6">

            {{-- Iniciály ležia pod obrázkom, nie vedľa neho: kanál si avatar
                 nesie len ako meno súboru a ten na disku chýbať môže. --}}
            <span class="ar-shell__avatar" aria-hidden="true">
                {{ $organization->initialName }}

                @if ($organization->avatar)
                    <img src="{{ Storage::url('organizations/' . $organization->id . '/' . $organization->avatar) }}"
                         alt="" loading="lazy" onerror="this.remove()">
                @endif
            </span>

            <div class="min-w-0 flex-1">
                {{-- Na nástenke je nadpisom samotný kanál, v sekciách sa meno
                     kanála posunie nad nadpis — inak by správca s viacerými
                     kanálmi nevedel, ktorý práve upravuje. --}}
                @if ($section !== 'dashboard')
                    <p class="ar-kicker">{{ $organization->title }}</p>
                @endif

                <h1 class="ar-display truncate text-2xl font-bold">{{ $heading }}</h1>

                @isset($lead)
                    <p class="mt-1 text-sm text-gray-500">{{ $lead }}</p>
                @endisset
            </div>

            <div class="flex flex-wrap items-center gap-2">
                {{ $actions ?? '' }}
            </div>
        </div>

        {{-- Sekcie správy kanála. Nahrádzajú bočné menu, ktoré nosili staršie
             stránky profilu — na širokom výpise by ukrojilo štvrtinu šírky pre
             šesť odkazov. --}}
        <nav class="flex flex-wrap items-center gap-2 py-5" aria-label="Správa kanála">
            @foreach ($tabs as $key => $item)
                @if ($key === $section)
                    <span class="ar-tab ar-tab--on" aria-current="page">
                        <i class="{{ $item['icon'] }}"></i> {{ $item['label'] }}
                        @isset($item['count'])
                            <span class="ar-badge ar-badge--count">{{ $num($item['count']) }}</span>
                        @endisset
                    </span>
                @else
                    <a href="{{ $item['url'] }}" class="ar-tab">
                        <i class="{{ $item['icon'] }}"></i> {{ $item['label'] }}
                        @isset($item['count'])
                            <span class="ar-badge ar-badge--count">{{ $num($item['count']) }}</span>
                        @endisset
                    </a>
                @endif
            @endforeach
        </nav>
    </div>
</header>

<div class="mx-auto max-w-6xl px-4 py-8">
    {{ $slot }}
</div>

<x-cards.card :title="'Liturgia dňa'" :icon="'components.icons.book'">
    {{-- Dáta: App\Services\Liturgy\DailyReadings. Farba sa kreslí inline —
         Tailwind triedy poskladané v PHP by do CSS nevygeneroval. --}}
    @php
        $color   = $day->color();
        $section = $day->mainSection();
    @endphp

    <div class="h-1.5" style="background: {{ $color->hex() }}" title="Liturgická farba: {{ $color->label() }}"></div>

    <div class="card_body py-3">
        <p class="text-xs uppercase tracking-wide text-gray-500">
            {{ $day->calendar->date->translatedFormat('l, j. F Y') }}
        </p>

        <h3 class="mt-1 flex items-start gap-2 text-lg font-bold leading-snug text-gray-800">
            <span class="mt-2 inline-block h-2.5 w-2.5 shrink-0 rounded-full ring-1 ring-black/10"
                  style="background: {{ $color->hex() }}" aria-hidden="true"></span>
            <span>{{ $day->title() }}</span>
        </h3>

        @if ($day->subtitle())
            <p class="text-sm text-gray-600">{{ $day->subtitle() }}</p>
        @endif

        @if ($day->obligation())
            <p class="mt-1 inline-block rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">
                Prikázaný sviatok
            </p>
        @endif

        <p class="mt-1 text-xs text-gray-500">{{ implode(' · ', $day->calendar->contextParts()) }}</p>

        @if ($section)
            <ul class="mt-3 space-y-2 text-sm">
                @foreach ($section['lines'] as $line)
                    @continue($line['type'] === 'sequence')

                    <li class="{{ $line['type'] === 'gospel' ? '-mx-2 rounded bg-gray-50 p-2' : '' }}">
                        <span class="block text-xs font-semibold uppercase text-gray-500">{{ $line['label'] }}</span>

                        <span class="font-semibold text-gray-800">
                            @foreach ($line['options'] as $option)
                                @unless ($loop->first)
                                    <span class="font-normal text-gray-500">{{ $option['label'] ?: 'alebo' }}</span>
                                @endunless
                                {{ $option['citation'] }}
                            @endforeach
                        </span>

                        @if ($line['type'] === 'gospel' && ! empty($line['options'][0]['heading']))
                            <span class="block italic text-gray-700">„{{ $line['options'][0]['heading'] }}“</span>
                        @endif

                        @if ($line['type'] === 'psalm' && $line['response'])
                            <span class="block text-gray-600">R.: {{ $line['response'] }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <p class="mt-3 text-sm text-gray-600">Citácie čítaní sa teraz nepodarilo načítať.</p>
        @endif

        <p class="mt-3 flex flex-wrap items-center justify-between gap-x-3 gap-y-1 text-sm">
            <a href="{{ route('readings.show') }}" class="font-semibold">
                Čítania na celý týždeň <i class="fa fa-angle-double-right" aria-hidden="true"></i>
            </a>
            <a href="{{ $day->sourceUrl }}" target="_blank" rel="noopener" class="text-gray-500 hover:underline">
                Plné znenie (KBS)
            </a>
        </p>

        @if ($homilies->isNotEmpty())
            <div class="mt-3 border-t border-gray-100 pt-2">
                <p class="text-xs font-semibold uppercase text-gray-500">Toto evanjelium v archíve</p>

                <ul class="mt-1 space-y-1 text-sm">
                    @foreach ($homilies as $homily)
                        <li>
                            <a href="{{ route('post.show', [$homily['id'], $homily['slug']]) }}" class="block truncate">
                                {{ $homily['title'] }}
                            </a>
                            <span class="text-xs text-gray-500">
                                {{ $homily['canal'] }} · pred {{ $homily['years_ago'] }} rokmi
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <p class="mt-3 border-t border-gray-100 pt-2 text-xs text-gray-500">
            @if ($milestone['days'] === 1)
                Zajtra: <strong>{{ $milestone['title'] }}</strong>
            @else
                Do {{ $milestone['until'] }} ešte <strong>{{ $milestone['days_label'] }}</strong>
            @endif
        </p>
    </div>
</x-cards.card>

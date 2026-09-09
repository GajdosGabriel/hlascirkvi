@php
    /*
     * Graf zhliadnutí za posledných 30 dní a pod ním pásik vydaných príspevkov
     * po mesiacoch. Oboje sa kreslí priamo do SVG na serveri — je to jedna
     * krivka a dvanásť obdĺžnikov, na to netreba grafovú knižnicu ani ďalší
     * dopyt z prehliadača.
     *
     * $timeline nesie 60 dní (dve okná); tu sa kreslí len to novšie, staršie
     * slúži na porovnanie v dlaždici nad grafom.
     */

    $days = $timeline->slice(\App\Services\Dashboard\DashboardStats::WINDOW)->values();

    // Rozmery sú v jednotkách viewBoxu, nie v pixeloch — SVG sa škáluje na
    // šírku panela a výška ide s ním.
    $w = 640; $h = 190;
    $padLeft = 42; $padTop = 12; $padBottom = 24;

    $plotWidth  = $w - $padLeft - 8;
    $plotHeight = $h - $padTop - $padBottom;

    // Horná hranica sa zaokrúhľuje nahor, nech popis osi nie je „147" ale
    // rozumné číslo. Nula je tu preto, aby graf prázdneho kanála nespadol
    // do delenia nulou.
    $peak  = max(1, (int) $days->max('views'));
    $step  = 10 ** max(0, strlen((string) $peak) - 2);
    $top   = (int) (ceil($peak / $step) * $step);

    $x = fn ($index) => $padLeft + ($days->count() > 1 ? $index * $plotWidth / ($days->count() - 1) : $plotWidth / 2);
    $y = fn ($views) => $padTop + $plotHeight - ($views / $top) * $plotHeight;

    $points = $days->map(fn ($day, $index) => round($x($index), 1) . ',' . round($y($day->views), 1));

    $baseline = $padTop + $plotHeight;
    $area = 'M ' . $x(0) . ',' . $baseline
        . ' L ' . $points->implode(' L ')
        . ' L ' . $x($days->count() - 1) . ',' . $baseline . ' Z';

    $peakDay = $days->sortByDesc('views')->first();

    // Mesačný pásik. Výška stĺpca je podiel z najsilnejšieho mesiaca, aby bolo
    // vidieť tempo, nie absolútne čísla.
    $monthsMax = max(1, (int) $activity->max('posts'));
    $monthNames = ['jan', 'feb', 'mar', 'apr', 'máj', 'jún', 'júl', 'aug', 'sep', 'okt', 'nov', 'dec'];
@endphp

<x-dashboard.panel title="Zhliadnutia za 30 dní" flush>
<x-slot name="note">
            @if ($views->current > 0)
                najsilnejší deň {{ \Carbon\Carbon::parse($peakDay->day)->format('j. n.') }}
                — {{ number_format($views->peak, 0, ',', ' ') }}
            @else
                zatiaľ bez nameraných zhliadnutí
            @endif
        </x-slot>

    <div class="ar-panel__body ar-chart__wrap">
        <svg class="ar-chart" viewBox="0 0 {{ $w }} {{ $h }}" role="img"
             aria-label="Zhliadnutia príspevkov kanála za posledných 30 dní">

            <defs>
                <linearGradient id="ar-chart-fill" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="rgba(var(--ar-accent-rgb), .20)"/>
                    <stop offset="100%" stop-color="rgba(var(--ar-accent-rgb), 0)"/>
                </linearGradient>
            </defs>

            {{-- Vodorovné vodidlá s popisom. Tri stačia: dno, polovica, strop. --}}
            @foreach ([0, 0.5, 1] as $ratio)
                @php $lineY = $padTop + $plotHeight * (1 - $ratio); @endphp
                <line class="ar-chart__grid" x1="{{ $padLeft }}" y1="{{ $lineY }}" x2="{{ $w - 8 }}" y2="{{ $lineY }}"/>
                <text class="ar-chart__label" x="{{ $padLeft - 6 }}" y="{{ $lineY + 3 }}" text-anchor="end">
                    {{ number_format($top * $ratio, 0, ',', ' ') }}
                </text>
            @endforeach

            <path class="ar-chart__area" d="{{ $area }}"/>
            <polyline class="ar-chart__line" points="{{ $points->implode(' ') }}"/>

            {{-- Posledný deň dostane bodku — je to „dnes", a bez nej krivka
                 na pravom okraji len tak končí. --}}
            <circle class="ar-chart__dot" cx="{{ $x($days->count() - 1) }}" cy="{{ $y($days->last()->views) }}" r="3"/>

            {{-- Popis osi len na krajoch a v strede; tridsať dátumov pod sebou
                 by sa aj tak neprečítalo. --}}
            @foreach ([0 => 'start', intdiv($days->count() - 1, 2) => 'middle', ($days->count() - 1) => 'end'] as $index => $anchor)
                <text class="ar-chart__label" x="{{ $x($index) }}" y="{{ $h - 6 }}" text-anchor="{{ $anchor }}">
                    {{ \Carbon\Carbon::parse($days[$index]->day)->format('j. n.') }}
                </text>
            @endforeach
        </svg>
    </div>

    <div class="border-t border-[color:var(--ar-line)] px-4 py-3">
        <div class="mb-2 flex items-baseline justify-between">
            <span class="ar-panel__title">Vydané príspevky po mesiacoch</span>
            <span class="ar-panel__note">{{ number_format($activity->sum('posts'), 0, ',', ' ') }} za rok</span>
        </div>

        <div class="ar-months">
            @foreach ($activity as $month)
                <div>
                    <span class="ar-months__bar {{ $month->posts === 0 ? 'ar-months__bar--empty' : '' }}"
                          style="height: {{ max(3, round($month->posts / $monthsMax * 44)) }}px"
                          title="{{ $monthNames[$month->month->month - 1] }} {{ $month->month->year }} — {{ $month->posts }}"></span>
                    <span class="ar-months__label">{{ $monthNames[$month->month->month - 1] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</x-dashboard.panel>

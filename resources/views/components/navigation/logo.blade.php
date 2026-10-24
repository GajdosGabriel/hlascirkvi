@use(App\Enums\SeasonalLogo)

@php
    // Šírka viewBoxu podľa toho, kam siahajú ozdoby; výška je vždy 56.
    $width = match ($logo) {
        SeasonalLogo::Plain => 0,
        SeasonalLogo::Snowy => 205,
        SeasonalLogo::Snowman => 202,
        SeasonalLogo::Advent => 166,
        SeasonalLogo::Christmas => 150,
        SeasonalLogo::Easter => 196,
        SeasonalLogo::Pentecost => 196,
        SeasonalLogo::Spring => 176,
        SeasonalLogo::Summer => 206,
        SeasonalLogo::Autumn => 208,
    };

    // textLength drží nápis rovnako široký v každom písme — ozdoby (sneh na
    // písmenách, plamienky) sú rozmiestnené podľa neho.
    $text = fn (int $x, int $y) => '<text x="'.$x.'" y="'.$y.'" font-size="20" fill="#fff" letter-spacing=".5" '
        .'textLength="106" lengthAdjust="spacingAndGlyphs">Hlas Cirkvi</text>';

    $flake = fn (int $x, int $y, float $scale = 1) => '<g transform="translate('.$x.' '.$y.') scale('.$scale.')">'
        .'<line y1="-6" y2="6"/><line x1="-5.2" y1="-3" x2="5.2" y2="3"/><line x1="-5.2" y1="3" x2="5.2" y2="-3"/></g>';

    $flame = fn (float $cx, float $top, float $h, string $fill) => sprintf(
        '<path d="M%1$s %2$s C%3$s %4$s %3$s %5$s %1$s %5$s C%6$s %5$s %6$s %4$s %1$s %2$sZ" fill="%7$s"/>',
        $cx, $top, $cx + $h / 2, $top + $h * .6, $top + $h, $cx - $h / 2, $fill
    );

    $flower = fn (float $cx, float $cy, float $r, string $fill) => collect([[0, -3.5], [3.3, -1], [2, 3], [-2, 3], [-3.3, -1]])
        ->map(fn ($p) => '<circle cx="'.($cx + $p[0]).'" cy="'.($cy + $p[1]).'" r="'.$r.'" fill="'.$fill.'"/>')
        ->implode('')
        .'<circle cx="'.$cx.'" cy="'.$cy.'" r="1.7" fill="#fde047"/>';
@endphp

<a class="flex shrink-0 items-center rounded-md px-2 py-1 text-lg font-semibold tracking-wide text-white transition-colors hover:bg-blue-800"
    href="{{ url('/') }}" data-logo="{{ $logo->value }}">
    @if ($logo === SeasonalLogo::Plain)
        Hlas Cirkvi
    @else
        <svg viewBox="0 0 {{ $width }} 56" width="{{ round($width * 48 / 56) }}" height="48" class="-mx-1"
            role="img" aria-label="Hlas Cirkvi">
            <title>Hlas Cirkvi – {{ $logo->label() }}</title>
            @switch($logo)
                @case(SeasonalLogo::Snowy)
                    {{-- Sneh leží tesne nad veľkými písmenami (výška verzálok ~14,5 px). --}}
                    {!! $text(12, 41) !!}
                    <g fill="#fff">
                        <rect x="11" y="22" width="106" height="3" rx="1.5" />
                        <circle cx="14" cy="22" r="3" /><circle cx="22" cy="21" r="3.6" />
                        <circle cx="31" cy="22" r="2.8" /><circle cx="40" cy="21" r="3.4" />
                        <circle cx="50" cy="22" r="2.6" /><circle cx="60" cy="21" r="3.5" />
                        <circle cx="70" cy="22" r="2.8" /><circle cx="80" cy="20.8" r="3.6" />
                        <circle cx="90" cy="22" r="2.7" /><circle cx="100" cy="21" r="3.4" />
                        <circle cx="110" cy="22" r="3" />
                    </g>
                    <g fill="#fff" opacity=".85">
                        <circle cx="135" cy="14" r="1.4" /><circle cx="160" cy="36" r="1.2" />
                        <circle cx="178" cy="12" r="1.6" /><circle cx="198" cy="42" r="1.3" />
                        <circle cx="45" cy="9" r="1.2" /><circle cx="85" cy="7" r="1.4" />
                    </g>
                    <g stroke="#fff" stroke-width="1.3" stroke-linecap="round">
                        {!! $flake(150, 22) !!}
                        {!! $flake(190, 28, .7) !!}
                    </g>
                @break

                @case(SeasonalLogo::Snowman)
                    <ellipse cx="20" cy="50" rx="15" ry="2.5" fill="#fff" opacity=".5" />
                    <circle cx="20" cy="41" r="9" fill="#fff" />
                    <circle cx="20" cy="27" r="6.5" fill="#fff" />
                    <rect x="13" y="19" width="14" height="2.2" fill="#0f172a" />
                    <rect x="15.5" y="11" width="9" height="8.5" fill="#0f172a" />
                    <rect x="15.5" y="16.5" width="9" height="1.6" fill="#dc2626" />
                    <circle cx="18" cy="26" r=".9" fill="#0f172a" />
                    <circle cx="22" cy="26" r=".9" fill="#0f172a" />
                    <path d="M20 28 L27 29.4 L20 30.2z" fill="#f97316" />
                    <rect x="13.5" y="32" width="13" height="3" rx="1.2" fill="#dc2626" />
                    <rect x="22" y="33" width="3" height="7" rx="1" fill="#dc2626" />
                    <circle cx="20" cy="39" r=".9" fill="#0f172a" />
                    <circle cx="20" cy="44" r=".9" fill="#0f172a" />
                    {!! $text(40, 36) !!}
                    <g fill="#fff" opacity=".85">
                        <circle cx="162" cy="14" r="1.4" /><circle cx="176" cy="38" r="1.2" />
                        <circle cx="196" cy="20" r="1.6" /><circle cx="60" cy="10" r="1.2" />
                        <circle cx="110" cy="8" r="1.4" />
                    </g>
                @break

                @case(SeasonalLogo::Advent)
                    {{-- Tretia sviečka je ružová (Gaudete) a zapaľuje sa v 3. týždni. --}}
                    @foreach (['#7c3aed', '#7c3aed', '#f472b6', '#7c3aed'] as $i => $color)
                        <rect x="{{ 10 + $i * 9 }}" y="30" width="5" height="14" rx="1" fill="{{ $color }}" />
                        @if ($i < $candles)
                            {!! $flame(12.5 + $i * 9, 21, 8, '#fbbf24') !!}
                        @endif
                    @endforeach
                    <ellipse cx="26" cy="45" rx="21" ry="5" fill="#15803d" />
                    <circle cx="12" cy="44" r="1.3" fill="#dc2626" />
                    <circle cx="30" cy="47" r="1.3" fill="#dc2626" />
                    <circle cx="42" cy="43" r="1.3" fill="#dc2626" />
                    {!! $text(54, 36) !!}
                @break

                @case(SeasonalLogo::Christmas)
                    {!! $text(12, 38) !!}
                    <path d="M134 6 L136.2 14 L145 16 L136.2 18 L134 34 L131.8 18 L123 16 L131.8 14Z" fill="#fcd34d" />
                    <g fill="#fde68a">
                        <circle cx="112" cy="10" r="1" /><circle cx="60" cy="12" r="1.1" />
                        <circle cx="90" cy="8" r="1.2" /><circle cx="25" cy="10" r="1" />
                    </g>
                    <path d="M12 45 Q60 41 118 45" stroke="#15803d" stroke-width="2.5" fill="none" stroke-linecap="round" />
                    <circle cx="40" cy="44" r="1.6" fill="#dc2626" />
                    <circle cx="75" cy="43.2" r="1.6" fill="#fcd34d" />
                    <circle cx="105" cy="44" r="1.6" fill="#dc2626" />
                @break

                @case(SeasonalLogo::Easter)
                    <g stroke="#fcd34d" stroke-width="1.2" opacity=".55">
                        <line x1="20" y1="22" x2="20" y2="4" /><line x1="20" y1="22" x2="6" y2="10" />
                        <line x1="20" y1="22" x2="34" y2="10" /><line x1="20" y1="22" x2="2" y2="22" />
                        <line x1="20" y1="22" x2="38" y2="22" />
                    </g>
                    <ellipse cx="20" cy="51" rx="18" ry="5" fill="#16a34a" />
                    <rect x="18" y="12" width="4" height="36" rx="1" fill="#fcd34d" />
                    <rect x="11" y="19" width="18" height="4" rx="1" fill="#fcd34d" />
                    {!! $text(44, 36) !!}
                    <ellipse cx="170" cy="30" rx="6.5" ry="8.5" fill="#f472b6" />
                    <path d="M163.8 29 l2 -2 l2 2 l2 -2 l2 2 l2 -2 l2 2" stroke="#fff" stroke-width="1.2" fill="none" />
                    <ellipse cx="186" cy="34" rx="5.5" ry="7" fill="#fbbf24" />
                    <circle cx="186" cy="34" r="1.6" fill="#fff" />
                @break

                @case(SeasonalLogo::Pentecost)
                    {!! $text(12, 40) !!}
                    @foreach ([[20, 9], [48, 7], [78, 9], [106, 7]] as [$cx, $top])
                        {!! $flame($cx, $top, 11, '#f97316') !!}
                        {!! $flame($cx, $top + 4, 6, '#fde047') !!}
                    @endforeach
                    <path d="M150 28 C156 22 166 22 170 26 C176 20 186 18 192 22 C184 24 180 28 176 30 C180 32 184 34 186 36 C178 36 172 34 168 32 C162 34 156 33 150 28Z" fill="#fff" />
                    <circle cx="154" cy="27" r=".9" fill="#1e3a8a" />
                @break

                @case(SeasonalLogo::Spring)
                    {!! $text(12, 36) !!}
                    <g stroke="#4ade80" stroke-width="1.5">
                        <line x1="136" y1="30" x2="136" y2="50" /><line x1="152" y1="38" x2="152" y2="50" />
                        <line x1="168" y1="26" x2="168" y2="50" />
                    </g>
                    <ellipse cx="140" cy="44" rx="4" ry="1.8" fill="#4ade80" transform="rotate(-30 140 44)" />
                    <ellipse cx="164" cy="42" rx="4" ry="1.8" fill="#4ade80" transform="rotate(30 164 42)" />
                    {!! $flower(136, 30, 2.6, '#f9a8d4') !!}
                    {!! $flower(152, 38, 2.4, '#fff') !!}
                    {!! $flower(168, 26, 2.6, '#c4b5fd') !!}
                    <path d="M12 45 Q60 42 118 45" stroke="#4ade80" stroke-width="2" fill="none" stroke-linecap="round" opacity=".7" />
                @break

                @case(SeasonalLogo::Summer)
                    <g stroke="#fbbf24" stroke-width="2.2" stroke-linecap="round">
                        <line x1="22" y1="12" x2="22" y2="8" /><line x1="22" y1="44" x2="22" y2="48" />
                        <line x1="6" y1="28" x2="10" y2="28" /><line x1="34" y1="28" x2="38" y2="28" />
                        <line x1="10.7" y1="16.7" x2="13.5" y2="19.5" /><line x1="30.5" y1="36.5" x2="33.3" y2="39.3" />
                        <line x1="10.7" y1="39.3" x2="13.5" y2="36.5" /><line x1="30.5" y1="19.5" x2="33.3" y2="16.7" />
                    </g>
                    <circle cx="22" cy="28" r="9" fill="#fbbf24" />
                    {!! $text(46, 36) !!}
                    <path d="M166 40 q6 -4 12 0 t12 0 t12 0" stroke="#7dd3fc" stroke-width="2" fill="none" stroke-linecap="round" />
                    <path d="M172 46 q6 -4 12 0 t12 0" stroke="#7dd3fc" stroke-width="2" fill="none" stroke-linecap="round" opacity=".6" />
                @break

                @case(SeasonalLogo::Autumn)
                    {!! $text(12, 36) !!}
                    <ellipse cx="128" cy="20" rx="6" ry="3" fill="#ea580c" transform="rotate(-35 128 20)" />
                    <ellipse cx="148" cy="14" rx="6" ry="3" fill="#dc2626" transform="rotate(25 148 14)" />
                    <ellipse cx="162" cy="34" rx="6.5" ry="3.2" fill="#f59e0b" transform="rotate(-20 162 34)" />
                    <ellipse cx="184" cy="22" rx="5.5" ry="2.8" fill="#b45309" transform="rotate(40 184 22)" />
                    <ellipse cx="200" cy="42" rx="6" ry="3" fill="#ea580c" transform="rotate(10 200 42)" />
                    <ellipse cx="40" cy="47" rx="5" ry="2.5" fill="#f59e0b" transform="rotate(15 40 47)" />
                    <g stroke="#1e3a8a" stroke-width=".7" opacity=".6">
                        <line x1="123" y1="23.5" x2="133" y2="16.5" />
                        <line x1="142.5" y1="11.5" x2="153.5" y2="16.5" />
                        <line x1="156" y1="36" x2="168" y2="32" />
                    </g>
                @break
            @endswitch
        </svg>
    @endif
</a>

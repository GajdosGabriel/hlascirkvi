{{-- Zmena proti predchádzajúcemu rovnako dlhému oknu.

     $change je celé percento, alebo null — vtedy sa nedá porovnávať, lebo
     predchádzajúce okno bolo nulové a percento z nuly nič nehovorí. --}}
@if ($change === null)
    <span class="ar-delta ar-delta--flat" title="Nie je s čím porovnať">—</span>
@elseif ($change > 0)
    <span class="ar-delta ar-delta--up" title="Oproti predchádzajúcim 30 dňom">
        <i class="fas fa-arrow-up text-[.55rem]"></i> {{ $change }} %
    </span>
@elseif ($change < 0)
    <span class="ar-delta ar-delta--down" title="Oproti predchádzajúcim 30 dňom">
        <i class="fas fa-arrow-down text-[.55rem]"></i> {{ abs($change) }} %
    </span>
@else
    <span class="ar-delta ar-delta--flat" title="Oproti predchádzajúcim 30 dňom">bez zmeny</span>
@endif

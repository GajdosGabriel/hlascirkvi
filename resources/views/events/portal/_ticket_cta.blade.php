{{-- Malé tlačidlo „Kúpiť lístok" / „Rezervovať" na karte podujatia — to isté
     ako na portáli Event. Druh aj text posiela portál (ticket_cta), tu sa nič
     nerozhoduje. Vedie na registráciu na portáli, preto nové okno. --}}
@if ($cta = $event->ticketCta())
    <a href="{{ $event->ticketUrl() }}" target="_blank" rel="noopener nofollow"
       aria-label="{{ $cta['label'] }} – {{ $event->title() }}"
       class="group/cta inline-flex shrink-0 items-center gap-1 rounded-full py-0.5 pl-2 pr-1.5 text-[.7rem] font-semibold text-white no-underline shadow-sm transition hover:shadow-md hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 active:scale-[.97]
              {{ $cta['kind'] === 'buy'
                  ? 'bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus-visible:outline-blue-600'
                  : 'bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 focus-visible:outline-emerald-600' }}">
        <i class="fas fa-ticket-alt text-[.65rem] opacity-90" aria-hidden="true"></i>
        <span class="whitespace-nowrap">{{ $cta['label'] }}</span>
        <i class="fas fa-angle-double-right text-[.65rem] transition-transform duration-200 group-hover/cta:translate-x-0.5" aria-hidden="true"></i>
    </a>
@endif

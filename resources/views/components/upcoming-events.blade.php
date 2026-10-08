<x-cards.card :title="'Najbližšie akcie'" :icon="'components.icons.calendar'">
    {{-- Dáta: App\View\Components\UpcomingEvents — podujatia najbližšieho dňa. --}}
    @php
        $visible = $events->take(\App\View\Components\UpcomingEvents::VISIBLE);
        $more    = $events->slice(\App\View\Components\UpcomingEvents::VISIBLE);
    @endphp

    <div class="card_body py-3">
        <p class="text-xs uppercase tracking-wide text-gray-500">
            <time datetime="{{ $day->toDateString() }}">{{ $dayLabel() }}</time>
        </p>

        <ul class="-mx-1.5 mt-2 space-y-1">
            @foreach ($visible as $event)
                @include('events.portal._mini')
            @endforeach
        </ul>

        {{-- Zvyšok dňa rozbalí skryté zaškrtávacie pole (bez JavaScriptu).
             <details> by riadok s prepínačom nechal nad rozbaleným zoznamom;
             takto ostáva vždy naspodku. Zoznam aj popisky musia byť súrodenci
             poľa, inak na ne peer-checked nedosiahne. --}}
        <div class="flex flex-wrap items-center gap-x-3 text-sm font-semibold">
            @if ($more->isNotEmpty())
                <input type="checkbox" id="upcoming-events-more" class="peer sr-only">

                <ul class="-mx-1.5 mt-1 hidden w-full space-y-1 font-normal peer-checked:block">
                    @foreach ($more as $event)
                        @include('events.portal._mini')
                    @endforeach
                </ul>
            @endif

            <div class="mb-2 mt-3 w-full border-t border-gray-100"></div>

            @if ($more->isNotEmpty())
                <label for="upcoming-events-more"
                       class="cursor-pointer text-gray-700 hover:underline peer-checked:hidden peer-focus-visible:underline">
                    Zobraziť ďalšie ({{ $more->count() }})
                    <i class="ph ph-caret-down text-gray-500" aria-hidden="true"></i>
                </label>
                <label for="upcoming-events-more"
                       class="hidden cursor-pointer text-gray-700 hover:underline peer-checked:inline peer-focus-visible:underline">
                    Zobraziť menej
                    <i class="ph ph-caret-up text-gray-500" aria-hidden="true"></i>
                </label>
            @endif

            <a href="{{ route('akcie.index') }}" class="ml-auto shrink-0">
                Všetky akcie <i class="ph ph-caret-double-right" aria-hidden="true"></i>
            </a>
        </div>
    </div>
</x-cards.card>

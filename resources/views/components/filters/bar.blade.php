{{-- Lišta filtrov — prepínače vľavo, hľadanie vpravo. Popis v App\View\Components\Filters\Bar. --}}
@if ($options || $search !== null)
    <div {{ $attributes->merge(['class' => 'ar-filters']) }}>

        <div class="ar-filters__set">
            @foreach ($options as $key => $label)
                <a href="{{ $toggleUrl($key) }}"
                   class="ar-tab @if ($isOn($key)) ar-tab--on @endif"
                   @if ($isOn($key)) aria-pressed="true" title="Zrušiť filter" @else aria-pressed="false" @endif>
                    {{ $label }}
                    @if ($isOn($key))
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" class="h-3 w-3 opacity-70" aria-hidden="true">
                            <path d="M5 5l10 10M15 5L5 15" />
                        </svg>
                    @endif
                </a>
            @endforeach

            @if ($searchTerm() !== '')
                <a href="{{ $clearSearchUrl() }}" class="ar-tab ar-tab--on" title="Zrušiť hľadanie">
                    „{{ \Illuminate\Support\Str::limit($searchTerm(), 24) }}“
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" class="h-3 w-3 opacity-70" aria-hidden="true">
                        <path d="M5 5l10 10M15 5L5 15" />
                    </svg>
                </a>
            @endif

            @if ($anyActive())
                <a href="{{ $resetUrl() }}" class="ar-filters__reset">Zrušiť všetko</a>
            @endif
        </div>

        @if ($search !== null)
            <form method="GET" action="{{ $resetUrl() }}" role="search"
                  class="ar-search @if ($searchTerm() !== '') ar-search--open @endif">

                {{-- Hľadanie je bežný GET, zapnuté prepínače by inak z adresy vypadli. --}}
                @foreach ($hiddenFields() as $name => $value)
                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                @endforeach

                <input type="search" name="search" value="{{ $searchTerm() }}"
                       placeholder="{{ $search }}" aria-label="{{ $search }}">

                <button type="submit" aria-label="Hľadať">
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" class="h-4 w-4" aria-hidden="true">
                        <circle cx="9" cy="9" r="6" />
                        <path d="M13.5 13.5L17 17" />
                    </svg>
                </button>
            </form>
        @endif

    </div>
@endif

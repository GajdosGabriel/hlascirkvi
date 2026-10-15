@php
    // Po chybe validácie musí formulár ukázať, čo správca napísal, nie to,
    // čo je v databáze — preto všetko cez old() s hodnotou modelu ako náhradou.
    $value = fn ($field, $default = null) => old($field, $default);

    $dateValue = fn ($field, $date) => old($field, $date?->format('Y-m-d\TH:i'));

    $field = fn (string $name) => 'ar-field' . ($errors->has($name) ? ' ar-field--error' : '');
    $placementValue = $value('placement', $announcement->placement?->value) ?? $placements[0]->value ?? null;
@endphp

{{-- Chyby validácie vypisuje layouts/app na začiatku <main>; pri poli sa
     ukáže len krátka hláška. --}}
<form method="POST" action="{{ $action }}" class="space-y-5">
    @csrf
    @if ($announcement->exists)
        @method('PUT')
    @endif

    <section class="ar-panel">
        <div class="ar-panel__head"><h2 class="ar-panel__title">Text oznamu</h2></div>
        <div class="ar-panel__body ar-form">
            <div>
                <label class="ar-label" for="title">Názov <span class="ar-req">*</span></label>
                <input class="{{ $field('title') }}" type="text" id="title" name="title"
                       value="{{ $value('title', $announcement->title) }}"
                       maxlength="191" required autofocus
                       placeholder="Napr. Prenos polnočnej svätej omše">
                @error('title') <p class="ar-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="ar-label" for="body">Text</label>
                <textarea class="{{ $field('body') }}" id="body" name="body" rows="4" maxlength="2000"
                          aria-describedby="body-hint">{{ $value('body', $announcement->body) }}</textarea>
                <p class="ar-hint" id="body-hint">Doplňujúci text. Značky HTML sa nevykresľujú, zalomenia riadkov áno.</p>
                @error('body') <p class="ar-error">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-x-5 gap-y-4 sm:grid-cols-2">
                <div>
                    <label class="ar-label" for="link_url">Odkaz</label>
                    <input class="{{ $field('link_url') }}" type="url" id="link_url" name="link_url"
                           value="{{ $value('link_url', $announcement->link_url) }}"
                           maxlength="191" placeholder="https://www.hlascirkvi.sk/…">
                    @error('link_url') <p class="ar-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="ar-label" for="link_text">Popis odkazu</label>
                    <input class="{{ $field('link_text') }}" type="text" id="link_text" name="link_text"
                           value="{{ $value('link_text', $announcement->link_text) }}"
                           maxlength="60" placeholder="Zistiť viac">
                    @error('link_text') <p class="ar-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>
    </section>

    <section class="ar-panel">
        <div class="ar-panel__head"><h2 class="ar-panel__title">Kde a ako sa zobrazí</h2></div>
        <div class="ar-panel__body ar-form">
            <div class="grid gap-x-5 gap-y-4 sm:grid-cols-2">
                <div>
                    <label class="ar-label" for="placement">Umiestnenie <span class="ar-req">*</span></label>
                    <select class="{{ $field('placement') }}" id="placement" name="placement" required aria-describedby="placement-hint">
                        @foreach ($placements as $option)
                            <option value="{{ $option->value }}" @selected($placementValue === $option->value)>
                                {{ $option->label() }}
                            </option>
                        @endforeach
                    </select>
                    {{-- Popis len vybraného miesta (skript nižšie ho mení s výberom).
                         Pod poľom predtým stáli opisy všetkých miest naraz. --}}
                    <p class="ar-hint" id="placement-hint" data-placement-hint
                       data-hints="{{ collect($placements)->mapWithKeys(fn ($option) => [$option->value => $option->description()])->toJson(JSON_UNESCAPED_UNICODE) }}">
                        {{ collect($placements)->firstWhere('value', $placementValue)?->description() }}
                    </p>
                    @error('placement') <p class="ar-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="ar-label" for="variant">Farba <span class="ar-req">*</span></label>
                    <select class="{{ $field('variant') }}" id="variant" name="variant" required>
                        @foreach ($variants as $option)
                            <option value="{{ $option->value }}"
                                    @selected($value('variant', $announcement->variant?->value) === $option->value)>
                                {{ $option->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('variant') <p class="ar-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="sm:max-w-xs">
                <label class="ar-label" for="sort_order">Poradie</label>
                <input class="{{ $field('sort_order') }}" type="number" id="sort_order" name="sort_order" min="0" max="65535"
                       value="{{ $value('sort_order', $announcement->sort_order ?? 0) }}" aria-describedby="sort-hint">
                <p class="ar-hint" id="sort-hint">Na jednom mieste sa oznamy zoradia od najnižšieho čísla.</p>
                @error('sort_order') <p class="ar-error">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="ar-panel">
        <div class="ar-panel__head"><h2 class="ar-panel__title">Zobrazovanie</h2></div>
        <div class="ar-panel__body ar-form">
            <div class="grid gap-x-5 gap-y-4 sm:grid-cols-2">
                <div>
                    <label class="ar-label" for="published_from">Zobrazovať od</label>
                    <input class="{{ $field('published_from') }}" type="datetime-local" id="published_from" name="published_from"
                           value="{{ $dateValue('published_from', $announcement->published_from) }}" aria-describedby="from-hint">
                    <p class="ar-hint" id="from-hint">Prázdne pole znamená hneď.</p>
                    @error('published_from') <p class="ar-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="ar-label" for="published_until">Zobrazovať do</label>
                    <input class="{{ $field('published_until') }}" type="datetime-local" id="published_until" name="published_until"
                           value="{{ $dateValue('published_until', $announcement->published_until) }}" aria-describedby="until-hint">
                    <p class="ar-hint" id="until-hint">Prázdne pole znamená bez konca.</p>
                    @error('published_until') <p class="ar-error">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Skryté pole drží hodnotu aj pri odškrtnutom políčku — prehliadač
                 nezaškrtnuté checkboxy neodosiela vôbec. --}}
            <div class="ar-checks">
                <input type="hidden" name="active" value="0">
                <label class="ar-check">
                    <input type="checkbox" name="active" value="1"
                           @checked((bool) $value('active', $announcement->active ?? true))>
                    Zapnutý — oznam sa vypisuje na webe
                </label>

                <input type="hidden" name="dismissible" value="0">
                <label class="ar-check">
                    <input type="checkbox" name="dismissible" value="1"
                           @checked((bool) $value('dismissible', $announcement->dismissible ?? false))>
                    Návštevník si ho môže zavrieť
                </label>
            </div>
        </div>
    </section>

    <x-dashboard.form-bar :cancel="route('admin.announcement.index')" :submit="$submit"
        :note="$announcement->exists ? 'Zmeny sa prejavia hneď po uložení.' : 'Oznam sa vytvorí po kliknutí na tlačidlo.'" />
</form>

@push('scripts')
    <script nonce="{{ csp_nonce() }}">
        window.arReady(() => {
            const select = document.getElementById('placement');
            const hint = document.querySelector('[data-placement-hint]');
            if (!select || !hint) return;
            const hints = JSON.parse(hint.dataset.hints || '{}');
            select.addEventListener('change', () => { hint.textContent = hints[select.value] ?? ''; });
        });
    </script>
@endpush

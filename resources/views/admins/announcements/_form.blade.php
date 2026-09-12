@php
    // Po chybe validácie musí formulár ukázať, čo správca napísal, nie to,
    // čo je v databáze — preto všetko cez old() s hodnotou modelu ako náhradou.
    $value = fn ($field, $default = null) => old($field, $default);

    $dateValue = fn ($field, $date) => old($field, $date?->format('Y-m-d\TH:i'));
@endphp

{{-- Chyby validácie vypisuje layouts/app na začiatku <main>, netreba ich sem. --}}
<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($announcement->exists)
        @method('PUT')
    @endif

    <fieldset class="rounded-lg border bg-white p-6">
        <legend class="px-2 text-lg font-semibold">Text oznamu</legend>

        <div class="form-group">
            <label for="title">Názov *</label>
            <input class="form-control" type="text" id="title" name="title"
                   value="{{ $value('title', $announcement->title) }}"
                   maxlength="191" required autofocus
                   placeholder="Napr. Prenos polnočnej svätej omše">
        </div>

        <div class="form-group">
            <label for="body">Text</label>
            <textarea class="form-control" id="body" name="body" rows="4" maxlength="2000"
                      placeholder="Doplňujúci text oznamu. Značky HTML sa nevykresľujú, zalomenia riadkov áno.">{{ $value('body', $announcement->body) }}</textarea>
        </div>

        <div class="grid gap-x-6 sm:grid-cols-2">
            <div class="form-group">
                <label for="link_url">Odkaz</label>
                <input class="form-control" type="url" id="link_url" name="link_url"
                       value="{{ $value('link_url', $announcement->link_url) }}"
                       maxlength="191" placeholder="https://www.hlascirkvi.sk/…">
            </div>

            <div class="form-group">
                <label for="link_text">Popis odkazu</label>
                <input class="form-control" type="text" id="link_text" name="link_text"
                       value="{{ $value('link_text', $announcement->link_text) }}"
                       maxlength="60" placeholder="Zistiť viac">
            </div>
        </div>
    </fieldset>

    <fieldset class="rounded-lg border bg-white p-6">
        <legend class="px-2 text-lg font-semibold">Kde a ako sa zobrazí</legend>

        <div class="grid gap-x-6 sm:grid-cols-2">
            <div class="form-group">
                <label for="placement">Umiestnenie *</label>
                <select class="form-control" id="placement" name="placement" required>
                    @foreach ($placements as $option)
                        <option value="{{ $option->value }}"
                                @selected($value('placement', $announcement->placement?->value) === $option->value)>
                            {{ $option->label() }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-2 text-sm text-gray-600">
                    @foreach ($placements as $option)
                        <span class="block"><strong>{{ $option->label() }}</strong> — {{ $option->description() }}</span>
                    @endforeach
                </p>
            </div>

            <div class="form-group">
                <label for="variant">Farba *</label>
                <select class="form-control" id="variant" name="variant" required>
                    @foreach ($variants as $option)
                        <option value="{{ $option->value }}"
                                @selected($value('variant', $announcement->variant?->value) === $option->value)>
                            {{ $option->label() }}
                        </option>
                    @endforeach
                </select>

                <label for="sort_order" class="mt-4 block">Poradie</label>
                <input class="form-control" type="number" id="sort_order" name="sort_order" min="0" max="65535"
                       value="{{ $value('sort_order', $announcement->sort_order ?? 0) }}">
                <p class="mt-1 text-sm text-gray-600">Na jednom mieste sa oznamy zoradia od najnižšieho čísla.</p>
            </div>
        </div>
    </fieldset>

    <fieldset class="rounded-lg border bg-white p-6">
        <legend class="px-2 text-lg font-semibold">Zobrazovanie</legend>

        <div class="grid gap-x-6 sm:grid-cols-2">
            <div class="form-group">
                <label for="published_from">Zobrazovať od</label>
                <input class="form-control" type="datetime-local" id="published_from" name="published_from"
                       value="{{ $dateValue('published_from', $announcement->published_from) }}">
                <p class="mt-1 text-sm text-gray-600">Prázdne pole znamená hneď.</p>
            </div>

            <div class="form-group">
                <label for="published_until">Zobrazovať do</label>
                <input class="form-control" type="datetime-local" id="published_until" name="published_until"
                       value="{{ $dateValue('published_until', $announcement->published_until) }}">
                <p class="mt-1 text-sm text-gray-600">Prázdne pole znamená bez konca.</p>
            </div>
        </div>

        {{-- Skryté pole drží hodnotu aj pri odškrtnutom políčku — prehliadač
             nezaškrtnuté checkboxy neodosiela vôbec. --}}
        <div class="form-group">
            <input type="hidden" name="active" value="0">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="active" value="1"
                       @checked((bool) $value('active', $announcement->active ?? true))>
                <span>Zapnutý — oznam sa vypisuje na webe</span>
            </label>
        </div>

        <div class="form-group">
            <input type="hidden" name="dismissible" value="0">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="dismissible" value="1"
                       @checked((bool) $value('dismissible', $announcement->dismissible ?? false))>
                <span>Návštevník si ho môže zavrieť (zapamätá sa mu v prehliadači)</span>
            </label>
        </div>
    </fieldset>

    <div class="flex flex-wrap justify-end gap-3">
        <a class="btn btn-default" href="{{ route('admin.announcement.index') }}">Zrušiť</a>
        <button class="btn btn-primary" type="submit">{{ $submit }}</button>
    </div>
</form>

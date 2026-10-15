@php
    $field = fn (string $name) => 'ar-field' . ($errors->has($name) ? ' ar-field--error' : '');
    // aria-describedby len na prvky, ktoré naozaj existujú.
    $describe = fn (string $name, ?string $hint = null) => trim(($hint ?? '') . ($errors->has($name) ? " {$name}-error" : ''));
@endphp

<section class="ar-panel">
    <div class="ar-panel__head">
        <h2 class="ar-panel__title">Základné údaje</h2>
    </div>
    <div class="ar-panel__body ar-form">
        <div class="sm:max-w-md">
            <label class="ar-label" for="kind">Typ <span class="ar-req">*</span></label>
            <select id="kind" name="kind" class="{{ $field('kind') }}" required aria-describedby="{{ $describe('kind', 'kind-hint') }}">
                <option value="collection" @selected(old('kind', $seminar->kind) === 'collection')>Tematická kolekcia</option>
                <option value="seminar" @selected(old('kind', $seminar->kind) !== 'collection')>Seminár / podujatie</option>
            </select>
            <p class="ar-hint" id="kind-hint">Semináre a podujatia sa zobrazujú aj v archíve konferencií a pútí. Zaradenie videí do kolekcií je nepovinné.</p>
            @error('kind') <p class="ar-error" id="kind-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="ar-label" for="title">Názov <span class="ar-req">*</span></label>
            <input type="text" id="title" name="title" class="{{ $field('title') }}"
                   value="{{ old('title', $seminar->title) }}" minlength="3" maxlength="255" required autofocus
                   @error('title') aria-invalid="true" @enderror aria-describedby="{{ $describe('title') }}">
            @error('title') <p class="ar-error" id="title-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="ar-label" for="description">Popis</label>
            <textarea id="description" name="description" class="{{ $field('description') }}" rows="5"
                      placeholder="Predstavte kolekciu a jej obsah."
                      @error('description') aria-invalid="true" @enderror aria-describedby="{{ $describe('description') }}">{{ old('description', $seminar->description) }}</textarea>
            @error('description') <p class="ar-error" id="description-error">{{ $message }}</p> @enderror
        </div>

        <div class="sm:max-w-md">
            <label class="ar-label" for="youtube_playlist">Playlist YouTube</label>
            <input type="text" id="youtube_playlist" name="youtube_playlist" class="{{ $field('youtube_playlist') }} font-mono text-sm"
                   placeholder="PL… alebo adresa playlistu" value="{{ old('youtube_playlist', $seminar->youtube_playlist) }}" maxlength="255"
                   inputmode="url" spellcheck="false" autocapitalize="off"
                   @error('youtube_playlist') aria-invalid="true" @enderror
                   aria-describedby="{{ $describe('youtube_playlist', 'youtube-playlist-hint') }}">
            <p class="ar-hint" id="youtube-playlist-hint">Nepovinné. Odkaz alebo ID playlistu; videá potom načítate tlačidlom v správe kolekcie.</p>
            @error('youtube_playlist') <p class="ar-error" id="youtube_playlist-error">{{ $message }}</p> @enderror
        </div>
    </div>
</section>

<x-dashboard.form-bar :cancel="$cancelUrl ?? route('profile.canals.seminars.index', $canal->id)"
    :submit="$submitLabel ?? 'Uložiť zmeny'"
    :note="$seminar->exists ? 'Zmeny sa prejavia hneď po uložení.' : 'Kolekcia sa vytvorí ako koncept. Potom môžete pridať videá a zverejniť ju.'" />

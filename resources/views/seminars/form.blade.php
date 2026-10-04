<fieldset class="rounded-lg border bg-white p-6">
    <legend class="px-2 text-lg font-semibold">Základné údaje</legend>
    <div class="form-group">
        <label for="kind">Typ kolekcie *</label>
        <select id="kind" name="kind" class="form-control" required>
            <option value="collection" @selected(old('kind', $seminar->kind) === 'collection')>Tematická kolekcia</option>
            <option value="seminar" @selected(old('kind', $seminar->kind) !== 'collection')>Seminár / podujatie</option>
        </select>
        <p class="mt-1 text-sm text-gray-600">Semináre a podujatia sa zobrazujú aj v archíve konferencií a pútí. Zaradenie videí do kolekcií je nepovinné.</p>
        @error('kind') <span class="invalid-feedback">{{ $message }}</span> @enderror
    </div>
    <div class="form-group">
        <label for="title">Názov kolekcie *</label>
        <input type="text" id="title" name="title" class="form-control" placeholder="Názov kolekcie"
            value="{{ old('title', $seminar->title) }}" minlength="3" maxlength="255" required autofocus
            @error('title') aria-invalid="true" aria-describedby="title-error" @enderror>
        @error('title')
            <span id="title-error" class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-group">
        <label for="description">Popis kolekcie</label>
        <textarea id="description" name="description" class="form-control" rows="5" placeholder="Predstavte kolekciu a jej obsah."
            @error('description') aria-invalid="true" aria-describedby="description-error" @enderror>{{ old('description', $seminar->description) }}</textarea>
        @error('description')
            <span id="description-error" class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-group">
        <label for="youtube_playlist">Playlist YouTube · nepovinné</label>
        <input type="text" id="youtube_playlist" name="youtube_playlist" class="form-control" placeholder="PL…"
            value="{{ old('youtube_playlist', $seminar->youtube_playlist) }}" maxlength="255"
            aria-describedby="youtube-playlist-help @error('youtube_playlist') youtube-playlist-error @enderror"
            @error('youtube_playlist') aria-invalid="true" @enderror>
        <p id="youtube-playlist-help" class="mt-1 text-sm text-gray-600">Zadajte odkaz alebo ID playlistu. Videá potom načítate tlačidlom v správe kolekcie.</p>
        @error('youtube_playlist')
            <span id="youtube-playlist-error" class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>
</fieldset>

<x-dashboard.form-bar :cancel="route('profile.canals.seminars.index', $canal->id)"
    :submit="$submitLabel ?? 'Uložiť zmeny'"
    :note="$seminar->exists ? 'Zmeny sa prejavia hneď po uložení.' : 'Kolekcia sa vytvorí ako koncept. Potom môžete pridať videá a zverejniť ju.'" />

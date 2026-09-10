<fieldset class="rounded-lg border bg-white p-6">
    <legend class="px-2 text-lg font-semibold">Základné údaje</legend>
    <div class="form-group">
        <label for="title">Názov seminára *</label>
        <input type="text" id="title" name="title" class="form-control" placeholder="Názov seminára"
            value="{{ old('title', $seminar->title) }}" minlength="3" maxlength="255" required autofocus
            @error('title') aria-invalid="true" aria-describedby="title-error" @enderror>
        @error('title')
            <span id="title-error" class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-group">
        <label for="description">Popis seminára</label>
        <textarea id="description" name="description" class="form-control" rows="5" placeholder="Predstavte seminár a jeho obsah."
            @error('description') aria-invalid="true" aria-describedby="description-error" @enderror>{{ old('description', $seminar->description) }}</textarea>
        @error('description')
            <span id="description-error" class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>
    <div class="form-group">
        <label for="youtube_playlist">ID playlistu YouTube</label>
        <input type="text" id="youtube_playlist" name="youtube_playlist" class="form-control" placeholder="PL…"
            value="{{ old('youtube_playlist', $seminar->youtube_playlist) }}" maxlength="255"
            aria-describedby="youtube-playlist-help @error('youtube_playlist') youtube-playlist-error @enderror"
            @error('youtube_playlist') aria-invalid="true" @enderror>
        <p id="youtube-playlist-help" class="mt-1 text-sm text-gray-600">Zadajte identifikátor playlistu začínajúci na PL.</p>
        @error('youtube_playlist')
            <span id="youtube-playlist-error" class="invalid-feedback">{{ $message }}</span>
        @enderror
    </div>
</fieldset>

<div class="flex flex-wrap justify-end gap-3">
    <a class="btn btn-default" href="{{ route('profile.canals.seminars.index', $canal->id) }}">Zrušiť</a>
    <button class="btn btn-primary" type="submit">{{ $submitLabel ?? 'Uložiť' }}</button>
</div>

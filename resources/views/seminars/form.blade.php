

    {{-- Title Field --}}
    <div class="form-group {{ $errors->has('title') ? ' invalid-feedback' : '' }}">
        <label class="font-semibold">Názov seminár</label>
        <input type="text" name="title" class="form-control" placeholder="Nový seminár ..." value="{{ old('title') ?? $seminar->title }}"
            required>

        @if ($errors->has('title'))
            <span class="invalid-feedback">
                {{ $errors->first('title') }}</span>
        @endif
    </div>

     {{-- Description Field --}}
     <div class="form-group {{ $errors->has('description') ? ' invalid-feedback' : '' }}">
        <label class="font-semibold">Popis seminára</label>
        <input type="text" name="description" class="form-control" placeholder="Popis seminár ..." value="{{ old('description') ?? $seminar->description }}">

        @if ($errors->has('description'))
            <span class="invalid-feedback">
                {{ $errors->first('description') }}</span>
        @endif
    </div>


      {{-- Playlist Field --}}
      <div class="form-group {{ $errors->has('youtube_playlist') ? ' invalid-feedback' : '' }}">
        <label class="font-semibold">Youtube playlist</label>
        <input type="text" name="youtube_playlist" class="form-control" placeholder="Začínajúci PL ..." value="{{ old('youtube_playlist') ?? $seminar->youtube_playlist }}">

        @if ($errors->has('youtube_playlist'))
            <span class="invalid-feedback">
                {{ $errors->first('youtube_playlist') }}</span>
        @endif
    </div>

    <div>
        <button class="btn btn-primary">Uložiť</button>
    </div>


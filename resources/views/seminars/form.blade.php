

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
        <input type="text" name="description" class="form-control" placeholder="Popis seminár ..." value="{{ old('description') ?? $seminar->description }}"
            required>

        @if ($errors->has('description'))
            <span class="invalid-feedback">
                {{ $errors->first('description') }}</span>
        @endif
    </div>

    <div>
        <button class="btn btn-primary">Uložiť</button>
    </div>


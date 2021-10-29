<form class="" method="post" action="{{ route('updaters.store') }}">
    @csrf @method('POST')

    {{-- Title Field --}}
    <div class="form-group {{ $errors->has('title') ? ' invalid-feedback' : '' }}">
        <input type="text" name="title" class="form-control" placeholder="Nový (updater)..." value="{{ old('title') }}"
            required>

        @if ($errors->has('title'))
            <span class="invalid-feedback">
                {{ $errors->first('title') }}</span>
        @endif
    </div>

    <div>
        <button class="btn btn-primary">Uložiť</button>
    </div>

</form>

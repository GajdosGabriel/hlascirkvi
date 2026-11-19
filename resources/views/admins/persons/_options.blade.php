{{-- Výber osoby: hodnota nesie číslo osoby, aby rovnaké mená nesplynuli. --}}
<datalist id="person-options">
    @foreach (\App\Models\Person::query()->orderBy('name')->get(['id', 'name']) as $option)
        <option value="{{ $option->name }} (#{{ $option->id }})">
    @endforeach
</datalist>

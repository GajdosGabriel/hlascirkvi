<label>Nadpis</label>
<input name="title" placeholder="Nadpis modlitby" value="{{ old('title') ?? $prayer->title }}"
    class="w-full mb-2 border-2 rounded p-2 border-gray-300" required />

<label>Modlitebná prosba</label>
<textarea name="body" rows="5" placeholder="Text modlitby" class="w-full mb-2 border-2 rounded p-2 border-gray-300"
    required>{{ old('body') ?? $prayer->body }}</textarea>

    <label>Uviesť zmenené, alebo anonymné meno</label>
<input name="user_name" placeholder="Anonimné meno" value="{{ old('user_name') ?? auth()->user()->first_name }}"
    class="w-full mb-2 border-2 rounded p-2 border-gray-300" required />

<div class="text-right ">
    <button class="btn btn-primary">Uložiť</button>
</div>

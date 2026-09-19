{{-- Staršie modlitby bez nadpisu sa dajú uložiť aj bez neho (SavePrayerRequest). --}}
@php($titleOptional = $prayer->exists && blank($prayer->title))
<label>Nadpis</label>
<input name="title" placeholder="Nadpis modlitby" value="{{ old('title') ?? $prayer->title }}"
    class="w-full mb-2 border-2 rounded p-2 border-gray-300" minlength="3" maxlength="255" @required(! $titleOptional) />

<label>Modlitebná prosba</label>
<textarea name="body" rows="5" placeholder="Text modlitby" class="w-full mb-2 border-2 rounded p-2 border-gray-300"
    required>{{ old('body') ?? $prayer->body }}</textarea>

    <label>Uviesť zmenené, alebo anonymné meno</label>
{{-- Pri úprave sa predtým ponúkalo meno prihláseného správcu, takže uloženie
     prepísalo meno, pod ktorým bola prosba zverejnená. --}}
<input name="user_name" placeholder="Anonimné meno" value="{{ old('user_name', $prayer->exists ? $prayer->user_name : auth()->user()->first_name) }}"
    class="w-full mb-2 border-2 rounded p-2 border-gray-300" maxlength="255" />

<x-dashboard.form-bar :cancel="route('profile.canals.prayers.index', $canal->id ?? auth()->user()->canal_id)"
    :submit="$prayer->exists ? 'Uložiť zmeny' : 'Pridať modlitbu'"
    :note="$prayer->exists ? 'Zmeny sa prejavia hneď po uložení.' : 'Modlitba sa pridá po kliknutí na tlačidlo.'" />

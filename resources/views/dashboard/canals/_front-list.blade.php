@if (auth()->user()->can('superadmin'))
    <div>
        <label class="ar-label" for="front_listed">Zobrazovanie v moduloch na úvodnej stránke</label>
        <select class="ar-field @error('front_listed') ar-field--error @enderror" id="front_listed" name="front_listed">
            <option value="0" @selected(! old('front_listed', isset($canal) && (bool) $canal->front_listed_at))>Nezobrazovať v moduloch</option>
            <option value="1" @selected(old('front_listed', isset($canal) && (bool) $canal->front_listed_at))>Zaradiť do modulov</option>
        </select>
        <p class="ar-hint">Režim identity určuje modul: Osobný → Kresťanské osobnosti, Organizácia → Cirkvi a spoločenstvá, Pseudonymný → Pseudonymné kanály.</p>
        <p class="ar-hint">Zmena sa prejaví po uložení. Zobrazujú sa iba zverejnené kanály; výber a poradie na úvodných kartách sú automatické.</p>
        @error('front_listed') <p class="ar-error">{{ $message }}</p> @enderror
        <p class="ar-hint"><a href="{{ route('admin.frontlist.index') }}">Spravovať zoznam osobností a spoločenstiev</a></p>
    </div>
@endif

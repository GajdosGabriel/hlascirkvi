{{-- Staršie modlitby bez nadpisu sa dajú uložiť aj bez neho (SavePrayerRequest). --}}
@php
    $titleOptional = $prayer->exists && blank($prayer->title);
    $field = fn (string $name) => 'ar-field' . ($errors->has($name) ? ' ar-field--error' : '');
    // aria-describedby len na prvky, ktoré naozaj existujú.
    $describe = fn (string $name, ?string $hint = null) => trim(($hint ?? '') . ($errors->has($name) ? " {$name}-error" : ''));
@endphp

<section class="ar-panel">
    <div class="ar-panel__head">
        <h2 class="ar-panel__title">Modlitebná prosba</h2>
    </div>
    <div class="ar-panel__body ar-form">
        <div>
            <label class="ar-label" for="prayer-title">
                Nadpis @unless ($titleOptional)<span class="ar-req">*</span>@endunless
            </label>
            <input id="prayer-title" name="title" class="{{ $field('title') }}" value="{{ old('title') ?? $prayer->title }}"
                   minlength="3" maxlength="255" @required(! $titleOptional)
                   @error('title') aria-invalid="true" @enderror aria-describedby="{{ $describe('title') }}">
            @error('title') <p class="ar-error" id="title-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="ar-label" for="prayer-body">Text prosby <span class="ar-req">*</span></label>
            <textarea id="prayer-body" name="body" rows="6" class="{{ $field('body') }}" required
                      @error('body') aria-invalid="true" @enderror aria-describedby="{{ $describe('body') }}">{{ old('body') ?? $prayer->body }}</textarea>
            @error('body') <p class="ar-error" id="body-error">{{ $message }}</p> @enderror
        </div>

        <div class="sm:max-w-sm">
            <label class="ar-label" for="prayer-user-name">Podpis pod prosbou</label>
            {{-- Pole sa nepredvypĺňa menom prihláseného správcu: popis sľubuje
                 zmenené alebo anonymné meno a kto si predvyplnenie nevšimol,
                 zverejnil pod prosbou svoje skutočné meno. Pri úprave ostáva
                 meno, pod ktorým bola prosba zverejnená. --}}
            <input id="prayer-user-name" name="user_name" class="{{ $field('user_name') }}" maxlength="255"
                   value="{{ old('user_name', $prayer->exists ? $prayer->user_name : '') }}" placeholder="napr. Anna"
                   @error('user_name') aria-invalid="true" @enderror aria-describedby="{{ $describe('user_name', 'prayer-user-name-hint') }}">
            <p class="ar-hint" id="prayer-user-name-hint">Zmenené alebo anonymné meno. Prázdne pole sa zobrazí ako „návštevník".</p>
            @error('user_name') <p class="ar-error" id="user_name-error">{{ $message }}</p> @enderror
        </div>

        @if ($prayer->exists)
            @can('superadmin')
                <div class="sm:max-w-sm">
                    <label class="ar-label" for="published">Dátum zverejnenia</label>
                    <input type="datetime-local" step="1" id="published" name="published" class="{{ $field('published') }}"
                           value="{{ old('published', $prayer->published?->format('Y-m-d\TH:i:s')) }}"
                           aria-describedby="{{ $describe('published', 'published-hint') }}">
                    <p class="ar-hint" id="published-hint">Prázdny dátum modlitbu skryje. Dátum vypočutia zostáva nezmenený.</p>
                    @error('published') <p class="ar-error" id="published-error">{{ $message }}</p> @enderror
                </div>
            @endcan
        @endif
    </div>
</section>

<x-dashboard.form-bar :cancel="route('profile.canals.prayers.index', $canal->id ?? auth()->user()->canal_id)"
    :submit="$prayer->exists ? 'Uložiť zmeny' : 'Pridať modlitbu'"
    :note="$prayer->exists ? 'Zmeny sa prejavia hneď po uložení.' : 'Modlitba sa pridá po kliknutí na tlačidlo.'" />

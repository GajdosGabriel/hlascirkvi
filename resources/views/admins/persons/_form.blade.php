@php
    $value = fn ($field, $default = null) => old($field, $default);
    $field = fn (string $name) => 'ar-field' . ($errors->has($name) ? ' ar-field--error' : '');
@endphp

<form method="POST" action="{{ $action }}" class="space-y-5">
    @csrf
    @if ($person->exists)
        @method('PUT')
    @endif

    <section class="ar-panel">
        <div class="ar-panel__head"><h2 class="ar-panel__title">Meno</h2></div>
        <div class="ar-panel__body ar-form">
            <div class="grid gap-x-5 gap-y-4 sm:grid-cols-4">
                <div>
                    <label class="ar-label" for="prefix">Titul pred</label>
                    <input class="{{ $field('prefix') }}" type="text" id="prefix" name="prefix" maxlength="60"
                           value="{{ $value('prefix', $person->prefix) }}" placeholder="Mons., o., Mgr.">
                </div>
                <div>
                    <label class="ar-label" for="first_name">Meno</label>
                    <input class="{{ $field('first_name') }}" type="text" id="first_name" name="first_name" maxlength="100"
                           value="{{ $value('first_name', $person->first_name) }}">
                </div>
                <div>
                    <label class="ar-label" for="last_name">Priezvisko <span class="ar-req">*</span></label>
                    <input class="{{ $field('last_name') }}" type="text" id="last_name" name="last_name" maxlength="100" required
                           value="{{ $value('last_name', $person->last_name) }}">
                    @error('last_name') <p class="ar-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="ar-label" for="title_suffix">Titul za</label>
                    <input class="{{ $field('title_suffix') }}" type="text" id="title_suffix" name="title_suffix" maxlength="60"
                           value="{{ $value('title_suffix', $person->title_suffix) }}" placeholder="SJ, PhD.">
                </div>
            </div>

            <div class="grid gap-x-5 gap-y-4 sm:grid-cols-2">
                <div>
                    <label class="ar-label" for="slug">Adresa na webe</label>
                    <input class="{{ $field('slug') }}" type="text" id="slug" name="slug" maxlength="120"
                           value="{{ $value('slug', $person->slug) }}" placeholder="vytvorí sa z mena" aria-describedby="slug-hint">
                    <p class="ar-hint" id="slug-hint">hlascirkvi.sk/osoba/<strong>…</strong> — malé písmená bez diakritiky a pomlčky.</p>
                    @error('slug') <p class="ar-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="ar-label" for="canal_id">Vlastný kanál</label>
                    <select class="{{ $field('canal_id') }}" id="canal_id" name="canal_id">
                        <option value="">— bez vlastného kanála —</option>
                        @foreach ($canalOptions as $canal)
                            <option value="{{ $canal->id }}" @selected((string) $value('canal_id', $person->canal_id) === (string) $canal->id)>{{ $canal->title }}</option>
                        @endforeach
                    </select>
                    @error('canal_id') <p class="ar-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="ar-label" for="description">Krátky popis</label>
                <input class="{{ $field('description') }}" type="text" id="description" name="description" maxlength="255"
                       value="{{ $value('description', $person->description) }}" placeholder="Napr. kazateľ Cirkevného zboru Kaplnka, spisovateľ">
                <p class="ar-hint">Zobrazí sa pod menom na stránke osoby.</p>
            </div>

            <div>
                <label class="ar-label" for="aliases">Iné podoby mena</label>
                <textarea class="{{ $field('aliases') }}" id="aliases" name="aliases" rows="4" maxlength="3000"
                          aria-describedby="aliases-hint">{{ $value('aliases', $person->exists ? $person->aliases->pluck('name')->implode("\n") : '') }}</textarea>
                <p class="ar-hint" id="aliases-hint">Jedna na riadok: prezývka, skloňovaný tvar, preklep („Pastýrčák“, „Ivana Eľka“).
                    Podľa nich sa osoba hľadá na webe aj rozpoznáva v nových videách.</p>
            </div>

            @if ($person->exists)
                <input type="hidden" name="verified" value="0">
                <label class="ar-check">
                    <input type="checkbox" name="verified" value="1" @checked((bool) $value('verified', $person->verified_at !== null))>
                    Overená — meno a priradenia sú skontrolované
                </label>
            @endif
        </div>
    </section>

    <x-dashboard.form-bar :cancel="route('admin.persons.index')" :submit="$submit" />
</form>

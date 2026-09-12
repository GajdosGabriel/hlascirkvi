@extends('layouts.dashboard')

@section('title')
    <title>{{ "Upraviť kanál {$canal->title}" }}</title>
@endsection

@section('content')

    @php
        $isAdmin = auth()->user()->hasRole('admin');
        $isSuperadmin = auth()->user()->can('superadmin');

        // Po neúspešnej validácii sa formulár vracia s tým, čo užívateľ
        // poslal, nie s tým, čo je v databáze.
        $selectedUpdaters = collect(old('updaters', $canal->updaters->pluck('id')->all()))
            ->map(fn ($id) => (int) $id);
        $denominationId = $selectedUpdaters
            ->intersect(($updaters['denomination'] ?? collect())->pluck('id'))
            ->first();
        $managerIds = collect(old('users', $canal->users->pluck('id')->all()))
            ->map(fn ($id) => (int) $id);
        $published = (bool) old('published', $canal->published);

        $field = fn (string $name) => 'ar-field' . ($errors->has($name) ? ' ar-field--error' : '');

        // Skupiny zaradenia, ktoré nastavuje len administrátor.
        $adminGroups = [
            'frontUser'          => ['Kanál na úvodnej stránke', 'Zobrazí sa v zozname kanálov na titulke.'],
            'post'               => ['Videá publikovať v zozname', 'Do ktorých výpisov sa zaradia nové videá kanála.'],
            'listOfOrganization' => ['Zaradený do zoznamu', null],
            'dayOfWeek'          => ['Vyhľadávanie videí v dňoch', 'V ktoré dni sa na YouTube hľadajú nové videá.'],
        ];
    @endphp

    <x-dashboard.frame>
        <x-dashboard.header :heading="$canal->title">
            <x-slot name="lead">Úprava údajov kanála. Polia označené <span class="ar-req">*</span> sú povinné.</x-slot>
            <x-slot name="actions">
                <a href="{{ route('organizations.show', $canal) }}" class="ar-btn ar-btn--quiet" target="_blank" rel="noopener">
                    <i class="fas fa-external-link-alt"></i> Zobraziť kanál
                </a>
                <a href="{{ route('profile.canals.index') }}" class="ar-btn ar-btn--quiet">
                    <i class="fas fa-arrow-left"></i> Späť na kanály
                </a>
            </x-slot>
        </x-dashboard.header>

        <form method="POST" action="{{ route('profile.canals.update', $canal) }}" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- Základné údaje --}}
            <section class="ar-panel">
                <div class="ar-panel__head">
                    <h2 class="ar-panel__title">Základné údaje</h2>
                </div>
                <div class="ar-panel__body ar-form">
                    <div>
                        <label class="ar-label" for="title">Názov kanála <span class="ar-req">*</span></label>
                        <input class="{{ $field('title') }}" type="text" id="title" name="title"
                               value="{{ old('title', $canal->title) }}" minlength="3" maxlength="191" required>
                        @error('title') <p class="ar-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="ar-label" for="description">Popis kanála</label>
                        <textarea class="{{ $field('description') }}" id="description" name="description" rows="5"
                                  placeholder="Predstavte svoj kanál a jeho obsah.">{{ old('description', $canal->description) }}</textarea>
                        <p class="ar-hint">Zobrazí sa v hlavičke verejného profilu kanála.</p>
                        @error('description') <p class="ar-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="ar-label" for="denomination">Cirkev / zaradenie kanála <span class="ar-req">*</span></label>
                        <select class="{{ $field('updaters') }}" id="denomination" name="updaters[]" required>
                            <option value="">Vyberte zaradenie</option>
                            @foreach ($updaters['denomination'] ?? [] as $updater)
                                <option value="{{ $updater->id }}" @selected($denominationId === $updater->id)>{{ $updater->title }}</option>
                            @endforeach
                        </select>
                        <p class="ar-hint">Určuje publikum, ktorému sa kanál ponúka.</p>
                        @error('updaters') <p class="ar-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            {{-- Adresa a kontakt --}}
            <section class="ar-panel">
                <div class="ar-panel__head">
                    <h2 class="ar-panel__title">Adresa a kontakt</h2>
                </div>
                <div class="ar-panel__body ar-form">
                    <div class="grid gap-x-5 gap-y-4 sm:grid-cols-2">
                        <div>
                            <label class="ar-label" for="village_id">Mesto / obec <span class="ar-req">*</span></label>
                            <select class="{{ $field('village_id') }}" id="village_id" name="village_id" required autocomplete="address-level2">
                                <option value="">Vyberte mesto alebo obec</option>
                                @foreach ($villages as $village)
                                    <option value="{{ $village->id }}" @selected((int) old('village_id', $canal->village_id) === $village->id)>
                                        {{ $village->fullname }} {{ $village->zip }}
                                    </option>
                                @endforeach
                            </select>
                            @error('village_id') <p class="ar-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="ar-label" for="street">Ulica a číslo</label>
                            <input class="{{ $field('street') }}" type="text" id="street" name="street"
                                   value="{{ old('street', $canal->street) }}" maxlength="191" autocomplete="street-address">
                            @error('street') <p class="ar-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="ar-label" for="email">Kontaktný e-mail</label>
                            <input class="{{ $field('email') }}" type="email" id="email" name="email"
                                   value="{{ old('email', $canal->email) }}" maxlength="100" autocomplete="email"
                                   placeholder="kontakt@example.sk">
                            @error('email') <p class="ar-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="ar-label" for="phone">Telefón</label>
                            <input class="{{ $field('phone') }}" type="tel" id="phone" name="phone"
                                   value="{{ old('phone', $canal->phone) }}" maxlength="20" autocomplete="tel"
                                   placeholder="+421 900 123 456">
                            @error('phone') <p class="ar-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="ar-label" for="url_www">Webová stránka</label>
                        <input class="{{ $field('url_www') }}" type="text" id="url_www" name="url_www" inputmode="url"
                               value="{{ old('url_www', $canal->url_www) }}" maxlength="191" autocomplete="url"
                               placeholder="www.farnost.sk">
                        {{-- Cast Urlwww z adresy nechá len schému a doménu. --}}
                        <p class="ar-hint">Uloží sa len adresa domény, napr. https://www.farnost.sk.</p>
                        @error('url_www') <p class="ar-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </section>

            {{-- Správcovia a zverejnenie --}}
            <section class="ar-panel">
                <div class="ar-panel__head">
                    <h2 class="ar-panel__title">Správcovia kanála</h2>
                    @if ($isSuperadmin)
                        <span class="ar-panel__note">Super admin</span>
                    @endif
                </div>
                <div class="ar-panel__body ar-form">
                    @if ($isSuperadmin)
                        <div>
                            <label class="ar-label" for="users">Správcovia <span class="ar-req">*</span></label>
                            <select class="{{ $field('users') }}" id="users" name="users[]" multiple required size="8">
                                @foreach ($users as $candidate)
                                    <option value="{{ $candidate->id }}" @selected($managerIds->contains($candidate->id))>
                                        {{ $candidate->fullname }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="ar-hint">Viac správcov vyberiete so stlačeným Ctrl (na Macu Cmd).</p>
                            @error('users') <p class="ar-error">{{ $message }}</p> @enderror
                        </div>

                        <label class="ar-toggle">
                            <input type="hidden" name="published" value="0">
                            <input type="checkbox" name="published" value="1" @checked($published)>
                            <span>
                                <span class="block text-sm font-semibold">Kanál je zverejnený</span>
                                <span class="ar-hint block">Nezverejnený kanál sa vo verejných výpisoch nezobrazuje.</span>
                            </span>
                        </label>
                    @else
                        <div class="flex flex-wrap gap-2">
                            @forelse ($canal->users as $manager)
                                <span class="ar-chip"><i class="fas fa-user"></i> {{ $manager->fullname }}</span>
                            @empty
                                <span class="ar-chip ar-chip--muted">Bez správcu</span>
                            @endforelse
                        </div>
                        <p class="ar-hint">Pridať alebo odobrať správcu môže administrátor portálu.</p>
                    @endif
                </div>
            </section>

            {{-- YouTube a zaradenie — len administrátor --}}
            @if ($isAdmin)
                <section class="ar-panel">
                    <div class="ar-panel__head">
                        <h2 class="ar-panel__title">YouTube a príspevky</h2>
                        <span class="ar-panel__note">Admin</span>
                    </div>
                    <div class="ar-panel__body ar-form">
                        @if ($canal->youtube_disabled_at)
                            {{-- Vypína App\Services\Youtube\DisableImport, keď zdroj na YouTube zmizne. --}}
                            <p class="ar-error mb-4">
                                Sťahovanie videí je vypnuté ({{ $canal->youtube_disabled_reason }}).
                                Zapne sa po uložení iného ID kanála alebo playlistu.
                            </p>
                        @endif

                        <div class="grid gap-x-5 gap-y-4 sm:grid-cols-2">
                            <div>
                                <label class="ar-label" for="youtube_channel">ID kanála YouTube</label>
                                <input class="{{ $field('youtube_channel') }} font-mono text-sm" type="text" id="youtube_channel"
                                       name="youtube_channel" value="{{ old('youtube_channel', $canal->youtube_channel) }}"
                                       maxlength="191" placeholder="UC… alebo adresa kanála" spellcheck="false">
                                @error('youtube_channel') <p class="ar-error">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="ar-label" for="youtube_playlist">ID playlistu YouTube</label>
                                <input class="{{ $field('youtube_playlist') }} font-mono text-sm" type="text" id="youtube_playlist"
                                       name="youtube_playlist" value="{{ old('youtube_playlist', $canal->youtube_playlist) }}"
                                       maxlength="191" placeholder="PL… alebo adresa playlistu" spellcheck="false">
                                @error('youtube_playlist') <p class="ar-error">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="sm:max-w-xs">
                            <label class="ar-label" for="mod_title">Text pred názvom príspevku</label>
                            <input class="{{ $field('mod_title') }}" type="text" id="mod_title" name="mod_title"
                                   value="{{ old('mod_title', $canal->mod_title) }}" maxlength="20">
                            <p class="ar-hint">Max. 20 znakov, napr. meno kazateľa.</p>
                            @error('mod_title') <p class="ar-error">{{ $message }}</p> @enderror
                        </div>

                        @foreach ($adminGroups as $type => [$label, $hint])
                            @continue(empty($updaters[$type]) || $updaters[$type]->isEmpty())
                            <fieldset>
                                <legend class="ar-label">{{ $label }}</legend>
                                <div class="ar-checks">
                                    @foreach ($updaters[$type] as $updater)
                                        <label class="ar-check">
                                            <input type="checkbox" name="updaters[]" value="{{ $updater->id }}"
                                                   @checked($selectedUpdaters->contains($updater->id))>
                                            {{ $updater->title }}
                                        </label>
                                    @endforeach
                                </div>
                                @if ($hint)
                                    <p class="ar-hint">{{ $hint }}</p>
                                @endif
                            </fieldset>
                        @endforeach
                    </div>
                </section>
            @endif

            <div class="ar-form__bar">
                <span class="ar-form__bar-note">Zmeny sa prejavia hneď po uložení.</span>
                <a href="{{ route('profile.canals.index') }}" class="ar-btn ar-btn--quiet">Zrušiť</a>
                <button type="submit" class="ar-btn ar-btn--accent"><i class="fas fa-check"></i> Uložiť zmeny</button>
            </div>
        </form>
    </x-dashboard.frame>
@endsection

    @php
        $creating = ! $canal->exists;
        $isAdmin = auth()->user()->can('admin');
        $isSuperadmin = auth()->user()->can('superadmin');

        // Po neúspešnej validácii sa formulár vracia s tým, čo užívateľ
        // poslal, nie s tým, čo je v databáze.
        $denomination = old('denomination', $canal->denomination?->value);
        $type         = old('identity_mode', $canal->identity_mode?->value);
        $section      = old('post_section', $canal->post_section?->value);
        $importDay    = old('import_day', $canal->import_day ?? (\App\Services\Youtube\VideoImportSchedule::hasSource($canal) ? null : 'auto'));

        $managerIds = collect(old('users', old('users_submitted') ? [] : $canal->users->pluck('id')->all()))
            ->map(fn ($id) => (int) $id);
        $published = old('published', $canal->published?->format('Y-m-d\TH:i:s'));

        $field = fn (string $name) => 'ar-field' . ($errors->has($name) ? ' ar-field--error' : '');
    @endphp
        <form method="POST" action="{{ $creating ? route('admin.canal.store') : route('profile.canals.update', $canal) }}" class="space-y-5">
            @csrf
            @unless ($creating)
                @method('PUT')
            @endunless

            {{-- Základné údaje --}}
            <section class="ar-panel">
                <div class="ar-panel__head">
                    <h2 class="ar-panel__title">Základné údaje</h2>
                </div>
                <div class="ar-panel__body ar-form">
                    <div>
                        <label class="ar-label" for="title">Názov kanála <span class="ar-req">*</span></label>
                        <input class="{{ $field('title') }}" type="text" id="title" name="title"
                               value="{{ old('title', $canal->title) }}" minlength="2" maxlength="191" required>
                        @error('title') <p class="ar-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="ar-label" for="description">Popis kanála</label>
                        {{-- HTML editor (TinyMCE). Starý popis je čistý text, editor
                             by mu zlial riadky — forEditor() ho rozdelí na odseky. --}}
                        <textarea class="{{ $field('description') }}" id="description" name="description" rows="8"
                                  placeholder="Predstavte svoj kanál a jeho obsah.">{{ \App\Support\SafeHtml::forEditor(old('description', $canal->description)) }}</textarea>
                        <p class="ar-hint">Zobrazí sa v hlavičke verejného profilu kanála. Povolené je základné formátovanie, zoznamy a odkazy.</p>
                        @error('description') <p class="ar-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="ar-label" for="denomination">Cirkev / zaradenie kanála</label>
                        <select class="{{ $field('denomination') }}" id="denomination" name="denomination">
                            <option value="">Vyberte zaradenie</option>
                            @foreach ($denominations as $option)
                                <option value="{{ $option->value }}" @selected($denomination === $option->value)>{{ $option->label() }}</option>
                            @endforeach
                        </select>
                        <p class="ar-hint">Určuje publikum, ktorému sa kanál ponúka.</p>
                        @error('denomination') <p class="ar-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="ar-label" for="identity_mode">Režim identity</label>
                        <select class="{{ $field('identity_mode') }}" id="identity_mode" name="identity_mode">
                            @foreach ($types as $option)
                                <option value="{{ $option->value }}" @selected($type === $option->value)>{{ $option->label() }}</option>
                            @endforeach
                        </select>
                        <p class="ar-hint">Osobná identita, organizácia alebo vystupovanie pod pseudonymom.</p>
                        @error('identity_mode') <p class="ar-error">{{ $message }}</p> @enderror
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
                        <input class="{{ $field('url_www') }}" type="text" id="url_www" name="url_www" inputmode="url" pattern="S+" autocapitalize="off" spellcheck="false"
                               value="{{ old('url_www', $canal->url_www) }}" maxlength="191" autocomplete="url"
                               placeholder="www.vasweb.sk">
                        {{-- Cast Urlwww z adresy nechá len schému a doménu. --}}
                        <p class="ar-hint">Uloží sa len adresa domény, napr. https://www.vasweb.sk.</p>
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
                        {{-- Výber správcov: vybraní ako štítky, ďalších pridá hľadanie
                             podľa mena. Pôvodný <select multiple> s Ctrl+klik nad
                             tisíckami mien sa nedal rozumne použiť. --}}
                        {{-- Zoznam mien je v atribúte, nie v <script type="application/json">:
                             Vue pri pripojení #app značky <script nonce="{{ csp_nonce() }}"> zo šablóny vyhodí. --}}
                        {{-- Kanál môže ostať bez správcu: tie, ktoré pridal príkaz
                             youtube:channels, nezaložil nikto z užívateľov. Značka
                             users_submitted povie controlleru, že prázdny výber je
                             zámer — bez nej by sa pole `users` vôbec neposlalo. --}}
                        <input type="hidden" name="users_submitted" value="1">
                        <div class="ar-picker" data-user-picker data-self="{{ auth()->id() }}"
                             data-users="{{ $users->map(fn ($u) => ['id' => $u->id, 'name' => auth()->user()->hasAnyRole(['admin', 'superadmin']) ? $u->email : $u->fullname])->values()->toJson(JSON_UNESCAPED_UNICODE) }}">
                            <label class="ar-label" for="manager-search">
                                Správcovia
                                <span class="ar-picker__count" data-picker-count></span>
                            </label>

                            <ul class="ar-picker__chosen" data-picker-chosen aria-live="polite">
                                @foreach ($users->whereIn('id', $managerIds) as $manager)
                                    <li class="ar-picker__chip" data-id="{{ $manager->id }}">
                                        <span class="ar-picker__avatar">{{ mb_substr($manager->first_name, 0, 1) }}{{ mb_substr($manager->last_name, 0, 1) }}</span>
                                        <span class="ar-picker__name">{{ auth()->user()->hasAnyRole(['admin', 'superadmin']) ? $manager->email : $manager->fullname }}</span>
                                        <input type="hidden" name="users[]" value="{{ $manager->id }}">
                                        <button type="button" class="ar-picker__remove" data-picker-remove
                                                aria-label="Odobrať {{ auth()->user()->hasAnyRole(['admin', 'superadmin']) ? $manager->email : $manager->fullname }}">&times;</button>
                                    </li>
                                @endforeach
                            </ul>
                            <p class="ar-picker__empty" data-picker-empty hidden>Kanál zatiaľ nemá správcu.</p>
                            <button type="button" class="ar-picker__self" data-picker-self hidden>
                                <i class="ph ph-user-plus"></i> Pridať mňa ako správcu
                            </button>

                            <div class="ar-picker__search">
                                <i class="ph ph-magnifying-glass ar-picker__icon" aria-hidden="true"></i>
                                <input class="{{ $field('users') }} ar-picker__input" type="search" id="manager-search"
                                       placeholder="Pridať správcu — začnite písať meno…" autocomplete="off" spellcheck="false"
                                       role="combobox" aria-expanded="false" aria-controls="manager-results" aria-autocomplete="list">
                                <ul class="ar-picker__results" id="manager-results" role="listbox" data-picker-results hidden></ul>
                            </div>
                            <p class="ar-hint">Ťuknutím na meno v zozname ho pridáte, krížikom pri štítku odoberiete. Šípky a Enter fungujú tiež.</p>
                            @error('users') <p class="ar-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="published" class="block text-sm font-semibold">Dátum zverejnenia</label>
                            <input class="{{ $field('published') }}" type="datetime-local" step="1"
                                   id="published" name="published" value="{{ $published }}">
                            <p class="ar-hint">Prázdny dátum znamená nezverejnený kanál. Vyplnený dátum eviduje zverejnenie, neslúži na jeho plánovanie.</p>
                            @error('published') <p class="ar-error">{{ $message }}</p> @enderror
                        </div>
                    @else
                        <div class="flex flex-wrap gap-2">
                            @forelse ($canal->users as $manager)
                                <span class="ar-chip"><i class="ph ph-user"></i> {{ auth()->user()->hasAnyRole(['admin', 'superadmin']) ? $manager->email : $manager->fullname }}</span>
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
                                       maxlength="191" inputmode="url" pattern="S+" placeholder="UC… alebo adresa kanála" spellcheck="false" autocapitalize="off">
                                @error('youtube_channel') <p class="ar-error">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="ar-label" for="youtube_playlist">ID playlistu YouTube</label>
                                <input class="{{ $field('youtube_playlist') }} font-mono text-sm" type="text" id="youtube_playlist"
                                       name="youtube_playlist" value="{{ old('youtube_playlist', $canal->youtube_playlist) }}"
                                       maxlength="191" inputmode="url" pattern="S+" placeholder="PL… alebo adresa playlistu" spellcheck="false" autocapitalize="off">
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

                        <div>
                            @if ($isSuperadmin)
                                <label class="ar-label" for="front_listed">Predný zoznam na úvodnej stránke</label>
                                <select class="{{ $field('front_listed') }}" id="front_listed" name="front_listed">
                                    <option value="0" @selected(! old('front_listed', (bool) $canal->front_listed_at))>Nezaradiť do predného zoznamu</option>
                                    <option value="1" @selected(old('front_listed', (bool) $canal->front_listed_at))>Zaradiť do predného zoznamu</option>
                                </select>
                                <p class="ar-hint">Zaradenie sa prejaví po uložení. Na verejnom zozname sa zobrazujú iba zverejnené kanály; výber na úvodné karty je automatický.</p>
                                @error('front_listed') <p class="ar-error">{{ $message }}</p> @enderror
                            @else
                                <span class="ar-label">Predný zoznam na úvodnej stránke</span>
                            @endif
                            <p class="ar-hint">
                                @if ($canal->front_listed_at)
                                    Kanál v prednom zozname je.
                                @else
                                    Kanál v prednom zozname nie je.
                                @endif
                                @if ($isSuperadmin)
                                    {{-- Správa zoznamu beží za checkSuperAdmin. --}}
                                    <a href="{{ route('admin.frontlist.index') }}">Spravovať zoznam</a>
                                @endif
                            </p>
                        </div>

                        <div class="sm:max-w-md">
                            <label class="ar-label" for="post_section">Kam idú nové videá kanála</label>
                            <select class="{{ $field('post_section') }}" id="post_section" name="post_section">
                                @foreach ($sections as $option)
                                    <option value="{{ $option->value }}" @selected($section === $option->value)>{{ $option->label() }}</option>
                                @endforeach
                            </select>
                            <p class="ar-hint">
                                {{ \App\Enums\CanalSection::tryFrom((string) $section)?->hint() ?? '' }}
                            </p>
                            @error('post_section') <p class="ar-error">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:max-w-md" data-video-schedule @if (! (filled(old('youtube_channel', $canal->youtube_channel)) || filled(old('youtube_playlist', $canal->youtube_playlist)))) hidden @endif>
                            <label class="ar-label" for="import_day">Deň kontroly videí</label>
                            <select class="{{ $field('import_day') }}" id="import_day" name="import_day"
                                    @disabled(! (filled(old('youtube_channel', $canal->youtube_channel)) || filled(old('youtube_playlist', $canal->youtube_playlist))))>
                                <option value="" @selected($importDay === null || $importDay === '')>Denne</option>
                                <option value="auto" @selected($importDay === 'auto')>Automaticky rozložiť — {{ $importDays[$suggestedImportDay] }}</option>
                                @foreach ($importDays as $cislo => $nazov)
                                    <option value="{{ $cislo }}" @selected((string) $importDay === (string) $cislo)>{{ $nazov }}</option>
                                @endforeach
                            </select>
                            <p class="ar-hint">Kontroluje nové videá zo zadaného YouTube kanála aj pripojeného playlistu. Vybraný deň znamená raz týždenne o 16:24. Pri pridaní YouTube zdroja sa automaticky vyberie najmenej obsadený deň. Môžete zvoliť aj konkrétny deň.</p>
                            @error('import_day') <p class="ar-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <span class="ar-label">Stav kontroly videí</span>
                            <p class="ar-hint">Posledná úspešná kontrola: {{ $canal->video_check_succeeded_at?->format('d. m. Y H:i') ?? 'Zatiaľ neevidovaná' }}.</p>
                            @if ($canal->youtube_disabled_at || $canal->post_section === \App\Enums\CanalSection::Paused)
                                <p class="ar-hint">Automatická kontrola je pozastavená.</p>
                            @elseif ($canal->video_check_next_at && $canal->video_check_next_at->isPast())
                                <p class="ar-hint">Čaká na najbližší denný beh{{ $canal->video_check_attempted_at?->isToday() ? ' zajtra' : '' }}.</p>
                            @else
                                <p class="ar-hint">Najbližšia kontrola: {{ ($canal->video_check_next_at ?? \App\Services\Youtube\VideoImportSchedule::nextDate($canal))?->format('d. m. Y H:i') ?? 'Nie je naplánovaná' }}.</p>
                            @endif
                            @if ($canal->name_search_window_end)
                                <p class="ar-hint">Vyhľadávanie je rozpracované, pokračovať bude pri ďalšom dennom behu.</p>
                            @endif
                            @if ($canal->video_check_error)
                                <p class="ar-error">{{ $canal->video_check_error }}</p>
                            @endif
                        </div>
                    </div>
                </section>
            @endif

            <x-dashboard.form-bar :cancel="$creating ? route('admin.canal.index') : route('profile.canals.index')" :submit="$creating ? 'Vytvoriť kanál' : 'Uložiť zmeny'" />
        </form>
    {{-- Ponuka editora zodpovedá značkám, ktoré nechá App\Support\SafeHtml. --}}
    @include('posts.editor', ['selector' => '#description', 'height' => 280])

    @if ($isAdmin)
        @push('scripts')
            <script nonce="{{ csp_nonce() }}">
                window.arReady(() => {
                    const source = document.getElementById('youtube_channel');
                    const playlist = document.getElementById('youtube_playlist');
                    const schedule = document.querySelector('[data-video-schedule]');
                    if (!source || !schedule) return;
                    const refresh = () => {
                        const visible = source.value.trim() !== '' || (playlist?.value.trim() ?? '') !== '';
                        schedule.hidden = !visible;
                        schedule.querySelector('select').disabled = !visible;
                    };
                    source.addEventListener('input', refresh);
                    source.addEventListener('change', refresh);
                    playlist?.addEventListener('input', refresh);
                    playlist?.addEventListener('change', refresh);
                    refresh();
                });
            </script>
        @endpush
    @endif

    @if ($isSuperadmin)
        @push('scripts')
            <script nonce="{{ csp_nonce() }}">
                // Až nad strom, ktorý Vue prekreslilo (partials/ar-ready) — inak by
                // poslucháče ostali na zahodených uzloch.
                window.arReady(() => document.querySelectorAll('[data-user-picker]').forEach((picker) => {
                    const users   = JSON.parse(picker.dataset.users || '[]');
                    const chosen  = picker.querySelector('[data-picker-chosen]');
                    const input   = picker.querySelector('.ar-picker__input');
                    const results = picker.querySelector('[data-picker-results]');
                    const empty   = picker.querySelector('[data-picker-empty]');
                    const count   = picker.querySelector('[data-picker-count]');
                    const self    = picker.querySelector('[data-picker-self]');
                    const LIMIT = 8;
                    let matches = [];
                    let active = -1;

                    // Hľadanie bez ohľadu na diakritiku a veľkosť písmen: „sulc" nájde „Šulc".
                    const fold = (s) => s.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
                    users.forEach((u) => { u.key = fold(u.name); });

                    const initials = (name) => name.split(/\s+/).filter(Boolean).slice(0, 2).map((w) => w[0]).join('');
                    const esc = (s) => s.replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
                    const selectedIds = () => new Set([...chosen.querySelectorAll('input')].map((i) => Number(i.value)));

                    // Zvýrazní nájdený úsek; pozícia sa hľadá v zloženom texte, dĺžka sa pri NFD
                    // bežnej slovenčiny zhoduje s originálom.
                    const highlight = (user, q) => {
                        const at = user.key.indexOf(q);
                        if (!q || at < 0) return esc(user.name);
                        return esc(user.name.slice(0, at)) + '<mark>' + esc(user.name.slice(at, at + q.length)) + '</mark>' + esc(user.name.slice(at + q.length));
                    };

                    const refresh = () => {
                        const n = chosen.children.length;
                        empty.hidden = n > 0;
                        count.textContent = n ? '(' + n + ')' : '';
                        self.hidden = selectedIds().has(Number(picker.dataset.self));
                    };

                    const close = () => {
                        results.hidden = true;
                        input.setAttribute('aria-expanded', 'false');
                        active = -1;
                    };

                    const setActive = (i) => {
                        const items = results.querySelectorAll('[role="option"]');
                        if (!items.length) return;
                        active = (i + items.length) % items.length;
                        items.forEach((el, k) => el.setAttribute('aria-selected', k === active ? 'true' : 'false'));
                        items[active].scrollIntoView({ block: 'nearest' });
                    };

                    const render = () => {
                        const q = fold(input.value.trim());
                        if (!q) { close(); return; }

                        const taken = selectedIds();
                        // Zhoda na začiatku slova má prednosť pred zhodou uprostred.
                        matches = users
                            .filter((u) => !taken.has(u.id) && u.key.includes(q))
                            .sort((a, b) => (b.key.startsWith(q) || b.key.includes(' ' + q)) - (a.key.startsWith(q) || a.key.includes(' ' + q)))
                            .slice(0, LIMIT);

                        results.innerHTML = matches.length
                            ? matches.map((u, i) =>
                                '<li class="ar-picker__option" role="option" aria-selected="false" data-index="' + i + '">' +
                                    '<span class="ar-picker__avatar">' + esc(initials(u.name)) + '</span>' +
                                    '<span>' + highlight(u, q) + '</span>' +
                                    '<span class="ar-picker__plus"><i class="ph ph-plus"></i> Pridať</span>' +
                                '</li>').join('')
                            : '<li class="ar-picker__none">Nikto s takým menom — alebo je už správcom.</li>';

                        results.hidden = false;
                        input.setAttribute('aria-expanded', 'true');
                        active = -1;
                        if (matches.length) setActive(0);
                    };

                    const add = (user) => {
                        const li = document.createElement('li');
                        li.className = 'ar-picker__chip';
                        li.dataset.id = user.id;
                        li.innerHTML =
                            '<span class="ar-picker__avatar">' + esc(initials(user.name)) + '</span>' +
                            '<span class="ar-picker__name">' + esc(user.name) + '</span>' +
                            '<input type="hidden" name="users[]" value="' + user.id + '">' +
                            '<button type="button" class="ar-picker__remove" data-picker-remove aria-label="Odobrať ' + esc(user.name) + '">&times;</button>';
                        chosen.appendChild(li);
                        input.value = '';
                        close();
                        refresh();
                        input.focus();
                    };

                    input.addEventListener('input', render);
                    input.addEventListener('focus', render);

                    input.addEventListener('keydown', (e) => {
                        if (e.key === 'ArrowDown') { e.preventDefault(); results.hidden ? render() : setActive(active + 1); }
                        else if (e.key === 'ArrowUp') { e.preventDefault(); setActive(active - 1); }
                        else if (e.key === 'Enter') {
                            // Enter v hľadaní nesmie odoslať celý formulár.
                            e.preventDefault();
                            if (!results.hidden && matches[active]) add(matches[active]);
                        }
                        else if (e.key === 'Escape') { close(); }
                        else if (e.key === 'Backspace' && !input.value && chosen.lastElementChild) {
                            chosen.lastElementChild.querySelector('[data-picker-remove]').focus();
                        }
                    });

                    // mousedown namiesto click, aby pole nestratilo fokus skôr, než sa výber zapíše.
                    results.addEventListener('mousedown', (e) => {
                        const option = e.target.closest('[role="option"]');
                        if (!option) return;
                        e.preventDefault();
                        add(matches[Number(option.dataset.index)]);
                    });
                    results.addEventListener('mousemove', (e) => {
                        const option = e.target.closest('[role="option"]');
                        if (option && Number(option.dataset.index) !== active) setActive(Number(option.dataset.index));
                    });

                    chosen.addEventListener('click', (e) => {
                        const button = e.target.closest('[data-picker-remove]');
                        if (!button) return;
                        button.closest('li').remove();
                        refresh();
                        input.focus();
                        if (input.value) render();
                    });

                    // Backspace z hľadania skočí na posledný štítok, ďalší Backspace ho odoberie.
                    chosen.addEventListener('keydown', (e) => {
                        if ((e.key === 'Backspace' || e.key === 'Delete') && e.target.matches('[data-picker-remove]')) {
                            e.preventDefault();
                            e.target.click();
                        }
                    });

                    document.addEventListener('click', (e) => { if (!picker.contains(e.target)) close(); });

                    self.addEventListener('click', () => {
                        const me = users.find((u) => u.id === Number(picker.dataset.self));
                        if (me) add(me);
                    });

                    refresh();
                }));
            </script>
        @endpush
    @endif

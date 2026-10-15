{{-- Štýly formulára sú v partials/admin-system: <style nonce="{{ csp_nonce() }}"> vnútri #app Vue
     pri kompilácii šablóny zahodí. --}}
@php
    $field = fn (string $name) => 'ar-field' . ($errors->has($name) ? ' ar-field--error' : '');
    // aria-describedby len na prvky, ktoré naozaj existujú.
    $describe = fn (string $name, ?string $hint = null) => trim(($hint ?? '') . ($errors->has($name) ? " {$name}-error" : ''));

    $collectionCanal = old('canal_id', $post->canal_id ?? ($canal->id ?? auth()->user()->canal_id));
    $collectionOptions = \App\Models\Seminar::without('canal')
        ->when(! auth()->user()->can('superadmin'), fn ($q) => $q->whereIn('canal_id', auth()->user()->canals()->pluck('canals.id')))
        ->orderBy('title')->get(['id', 'title', 'kind', 'canal_id']);
    $chosenCollections = session()->hasOldInput('collections_present')
        ? old('collections', []) : $post->seminars->modelKeys();
@endphp

{{-- Nadpis a text sú hlavný obsah, preto stoja prvé. Do 10/2026 bol nadpis
     až pod výpisom, videom, zverejnením a kolekciami a mal len placeholder. --}}
<section class="ar-panel">
    <div class="ar-panel__head">
        <h2 class="ar-panel__title">Článok</h2>
    </div>
    <div class="ar-panel__body ar-form">
        <div>
            <label class="ar-label" for="post-title">Nadpis <span class="ar-req">*</span></label>
            <input type="text" id="post-title" name="title" class="{{ $field('title') }} text-base font-semibold"
                   value="{{ old('title') ?? $post->title }}" minlength="3" maxlength="200" required
                   @error('title') aria-invalid="true" @enderror aria-describedby="{{ $describe('title') }}">
            @error('title') <p class="ar-error" id="title-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="ar-label" for="editor">Text</label>
            <textarea id="editor" name="body" class="{{ $field('body') }}"
                      aria-describedby="{{ $describe('body', 'body-hint') }}">{{ old('body') ?? $post->body }}</textarea>
            <p class="ar-hint" id="body-hint">Pri článku s videom je text nepovinný.</p>
            @error('body') <p class="ar-error" id="body-error">{{ $message }}</p> @enderror
        </div>

        <div class="sm:max-w-xl">
            <label class="ar-label" for="post-video-id">Video YouTube</label>
            <input type="text" id="post-video-id" name="video_id" value="{{ old('video_id') ?? $post->video_id }}"
                   class="{{ $field('video_id') }}" maxlength="191" inputmode="url" spellcheck="false" autocapitalize="off"
                   placeholder="https://www.youtube.com/watch?v=…"
                   @error('video_id') aria-invalid="true" @enderror aria-describedby="{{ $describe('video_id', 'video-hint') }}">
            <p class="ar-hint" id="video-hint">Nepovinné. Odkaz na video alebo jeho ID.</p>
            @error('video_id') <p class="ar-error" id="video_id-error">{{ $message }}</p> @enderror
        </div>
    </div>
</section>

<section class="ar-panel mt-5">
    <div class="ar-panel__head">
        <h2 class="ar-panel__title">Zaradenie a zverejnenie</h2>
    </div>
    <div class="ar-panel__body ar-form">
        <div class="grid gap-x-5 gap-y-4 md:grid-cols-2">
            <div>
                <label class="ar-label" for="post-section">Výpis <span class="ar-req">*</span></label>
                {{-- Do 9/2026 to bol zoznam updaterov typu `post` načítaný priamo
                     v šablóne, pričom ten istý záznam znamenal aj „príspevok je
                     zverejnený". Zaradenie je dnes stĺpec `posts.section`. --}}
                <select id="post-section" name="section" required class="{{ $field('section') }}"
                        @error('section') aria-invalid="true" @enderror aria-describedby="{{ $describe('section', 'section-hint') }}">
                    <option value="" disabled @selected(! old('section', $post->section?->value))>Vybrať výpis</option>
                    @foreach (\App\Enums\PostSection::options() as $option)
                        <option value="{{ $option->value }}" @selected(old('section', $post->section?->value) === $option->value)>
                            {{ $option->label() }}
                        </option>
                    @endforeach
                </select>
                <p class="ar-hint" id="section-hint">Kde na webe sa článok zobrazí.</p>
                @error('section') <p class="ar-error" id="section-error">{{ $message }}</p> @enderror
            </div>

            {{-- Prepínač posielal do poľa `published` buď dátum, alebo reťazec
                 „null". Pole nebolo vo validácii, takže ho PostSaveRequest zahodil
                 a voľba nerobila nič. Stav dnes nesie `published_at`. --}}
            <fieldset>
                <legend class="ar-label">Zverejnenie</legend>
                <div class="post-form__choice">
                    <label for="publishet1">
                        <input type="radio" value="1" @checked((bool) old('publish_now', $post->published_at))
                               required id="publishet1" name="publish_now">
                        Hneď
                    </label>
                    <label for="publishet2">
                        <input type="radio" value="0" @checked(! (bool) old('publish_now', $post->published_at))
                               required id="publishet2" name="publish_now" aria-describedby="publish-hint">
                        Zaradiť do buffera
                    </label>
                </div>
                <p class="ar-hint" id="publish-hint">Buffer púšťa príspevky postupne počas dňa.</p>
            </fieldset>
        </div>

        @can('admin')
            <div class="form-author sm:max-w-xl">
                @php
                    $selectedCanal = old('canal_id', $post->canal_id ?? auth()->user()->canal_id);
                    $selectableCanals = auth()->user()->can('superadmin')
                        ? \App\Models\Canal::orderBy('title')->get(['id', 'title'])
                        : auth()->user()->canals->sortBy('title')->values();
                @endphp
                <canal-select :canals='@json($selectableCanals->map(fn ($canal) => ["id" => $canal->id, "title" => $canal->title])->values())'
                    :selected='@json((string) $selectedCanal)'></canal-select>
                @error('canal_id') <p class="ar-error">{{ $message }}</p> @enderror
            </div>
        @endcan

        <collection-select :items='@json($collectionOptions)' :selected='@json($chosenCollections)'
            :canal-id='@json((string) $collectionCanal)' manage-base="{{ url('/dashboard/canals') }}"></collection-select>
        @error('collections') <p class="ar-error">{{ $message }}</p> @enderror
        @error('collections.*') <p class="ar-error">{{ $message }}</p> @enderror
    </div>
</section>

{{-- Obrázky: uložené aj novo vybrané v jednom komponente (resources/js/posts/PostImages.vue) --}}
<section class="ar-panel mt-5">
    <div class="ar-panel__head">
        <h2 class="ar-panel__title">Obrázky</h2>
    </div>
    <div class="ar-panel__body">
        <post-images name="pictures[]"
            :images='@json($post->images->map(fn ($image) => ['id' => $image->id, 'thumb' => $image->thumb_image_url, 'url' => $image->original_image_url])->values())'>
        </post-images>
        @error('pictures') <p class="ar-error">{{ $message }}</p> @enderror
        @error('pictures.*') <p class="ar-error">{{ $message }}</p> @enderror
    </div>
</section>

<x-dashboard.form-bar :cancel="route('profile.posts.index')"
    :submit="$post->exists ? 'Uložiť zmeny' : 'Vytvoriť článok'"
    :note="$post->exists ? 'Zmeny sa prejavia hneď po uložení.' : 'Článok sa vytvorí po kliknutí na tlačidlo.'" />

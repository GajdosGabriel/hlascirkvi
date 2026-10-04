{{-- Štýly formulára sú v partials/admin-system: <style nonce="{{ csp_nonce() }}"> vnútri #app Vue
     pri kompilácii šablóny zahodí. --}}
<div class="post-form__meta">

    <div class="form-category {{ $errors->has('section') ? ' has-error' : '' }}">
        <label for="post-section">Výpis</label>
        {{-- Do 9/2026 to bol zoznam updaterov typu `post` načítaný priamo
             v šablóne, pričom ten istý záznam znamenal aj „príspevok je
             zverejnený". Zaradenie je dnes stĺpec `posts.section`. --}}
        <select id="post-section" name="section" required class="form-control">
            <option value="" disabled @selected(! $post->section)>Vybrať výpis</option>
            @foreach (\App\Enums\PostSection::options() as $option)
                <option value="{{ $option->value }}" @selected(old('section', $post->section?->value) === $option->value)>
                    {{ $option->label() }}
                </option>
            @endforeach
        </select>
    </div>


    {{-- Video Link --}}
    <div class="form-category">
        <label for="post-video-id">Video YouTube</label>
        <input type="text" id="post-video-id" name="video_id" value="{{ old('video_id') ?? $post->video_id }}" class="form-control" maxlength="191" inputmode="url" spellcheck="false" autocapitalize="off"
            placeholder="Odkaz na video Youtube">
    </div>

    <fieldset class="form-category">
        <legend class="post-form__section-label">{{ trans('web.publish_now') }}</legend>

        {{-- Prepínač posielal do poľa `published` buď dátum, alebo reťazec
             „null". Pole nebolo vo validácii, takže ho PostSaveRequest zahodil
             a voľba nerobila nič. Stav dnes nesie `published_at`. --}}
        <div class="post-form__choice">
            <label for="publishet1">
                <input type="radio" value="1" @checked((bool) old('publish_now', $post->published_at))
                    required id="publishet1" name="publish_now">
                Teraz
            </label>

            <label for="publishet2">
                <input type="radio" value="0" @checked(! (bool) old('publish_now', $post->published_at))
                    required id="publishet2" name="publish_now">
                Nechať vo fronte
            </label>
        </div>
    </fieldset>

    @can('admin')
        <div class="form-author">
            @php
                $selectedCanal = old('canal_id', $post->canal_id ?? auth()->user()->canal_id);
                $selectableCanals = auth()->user()->can('superadmin')
                    ? \App\Models\Canal::orderBy('title')->get(['id', 'title'])
                    : auth()->user()->canals->sortBy('title')->values();
            @endphp
            <canal-select :canals='@json($selectableCanals->map(fn ($canal) => ["id" => $canal->id, "title" => $canal->title])->values())'
                :selected='@json((string) $selectedCanal)'></canal-select>
        </div>
    @endcan

    {{-- @can('admin') --}}
    {{-- <div class="form-author"> --}}
    {{-- <label>User - admin</label> --}}
    {{-- <select class="form-control" name="canal_id" required> --}}
    {{-- <option value="" selected disabled>Autor</option> --}}
    {{-- @foreach ($users as $user) --}}
    {{-- <option --}}
    {{-- @if (isset($post->canal_id) and $post->canal_id == $canal->id) --}}
    {{-- selected --}}
    {{-- @endif --}}
    {{-- value="{{ $canal->id }}">{{ $user->last_name . ' ' . $user->first_name }}</option> --}}
    {{-- @endforeach --}}
    {{-- </select> --}}
    {{-- </div> --}}
    {{-- @endcan --}}

</div>


{{-- Title Field --}}
@php
    $collectionCanal = old('canal_id', $post->canal_id ?? ($canal->id ?? auth()->user()->canal_id));
    $collectionOptions = \App\Models\Seminar::without('canal')
        ->when(! auth()->user()->can('superadmin'), fn ($q) => $q->whereIn('canal_id', auth()->user()->canals()->pluck('canals.id')))
        ->orderBy('title')->get(['id', 'title', 'kind', 'canal_id']);
    $chosenCollections = session()->hasOldInput('collections_present')
        ? old('collections', []) : $post->seminars->modelKeys();
@endphp
<collection-select :items='@json($collectionOptions)' :selected='@json($chosenCollections)'
    :canal-id='@json((string) $collectionCanal)' manage-base="{{ url('/dashboard/canals') }}"></collection-select>
@error('collections') <p class="invalid-feedback">{{ $message }}</p> @enderror
@error('collections.*') <p class="invalid-feedback">{{ $message }}</p> @enderror

<div class="form-group {{ $errors->has('title') ? ' invalid-feedback' : '' }}">
    <label for="post-title" class="sr-only">Nadpis</label>
    <input type="text" id="post-title" name="title" class="form-control" placeholder="Nadpis ..."
        value="{{ old('title') ?? $post->title }}" minlength="3" maxlength="200" required>
</div>


{{-- Text Field --}}
<div class="form-group {{ $errors->has('body') ? ' has-error' : '' }}">
    <textarea id="editor" name="body" placeholder="Text príspevku ...">{{ old('body') ?? $post->body }}</textarea>
    @if ($errors->has('body'))
        <span class="invalid-feedback">
            <strong>{{ $errors->first('body') }}</strong></span>
    @endif
</div>


{{-- Obrázky: uložené aj novo vybrané v jednom komponente (resources/js/posts/PostImages.vue) --}}
<div class="form-group post-form__images">
    <span class="post-form__section-label">Obrázky</span>
    <post-images name="pictures[]"
        :images='@json($post->images->map(fn ($image) => ['id' => $image->id, 'thumb' => $image->thumb_image_url, 'url' => $image->original_image_url])->values())'>
    </post-images>
</div>

<x-dashboard.form-bar :cancel="route('profile.posts.index')"
    :submit="$post->exists ? 'Uložiť zmeny' : 'Vytvoriť článok'"
    :note="$post->exists ? 'Zmeny sa prejavia hneď po uložení.' : 'Článok sa vytvorí po kliknutí na tlačidlo.'" />

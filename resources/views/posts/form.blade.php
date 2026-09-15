{{-- Štýly formulára sú v partials/admin-system: <style> vnútri #app Vue
     pri kompilácii šablóny zahodí. --}}
<div class="post-form__meta">

    <div class="form-category {{ $errors->has('section') ? ' has-error' : '' }}">
        <label>Výpis</label>
        {{-- Do 9/2026 to bol zoznam updaterov typu `post` načítaný priamo
             v šablóne, pričom ten istý záznam znamenal aj „príspevok je
             zverejnený". Zaradenie je dnes stĺpec `posts.section`. --}}
        <select name="section" required class="form-control">
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
        <label>Video YouTube</label>
        <input type="text" name="video_id" value="{{ old('video_id') ?? $post->video_id }}" class="form-control"
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
            <label>Kanál</label>
            <select class="form-control" name="organization_id" required>
                <option value="" selected disabled>Autor</option>
                @can('superadmin')
                    @foreach (\App\Models\Canal::orderBy('title', 'asc')->get() as $organization)
                        <option @if (isset($post->organization_id) and $post->organization_id == $organization->id or
                                $organization->id == auth()->user()->org_id) selected @endif value="{{ $organization->id }}">
                            {{ $organization->title }}
                        </option>
                    @endforeach
                @else
                    @foreach (auth()->user()->organizations as $organization)
                        <option @if (isset($post->organization_id) and $post->organization_id == $organization->id) selected @endif value="{{ $organization->id }}">
                            {{ $organization->title }}
                        </option>
                    @endforeach
                @endcan
            </select>
        </div>
    @endcan

    {{-- @can('admin') --}}
    {{-- <div class="form-author"> --}}
    {{-- <label>User - admin</label> --}}
    {{-- <select class="form-control" name="organization_id" required> --}}
    {{-- <option value="" selected disabled>Autor</option> --}}
    {{-- @foreach ($users as $user) --}}
    {{-- <option --}}
    {{-- @if (isset($post->organization_id) and $post->organization_id == $organization->id) --}}
    {{-- selected --}}
    {{-- @endif --}}
    {{-- value="{{ $organization->id }}">{{ $user->last_name . ' ' . $user->first_name }}</option> --}}
    {{-- @endforeach --}}
    {{-- </select> --}}
    {{-- </div> --}}
    {{-- @endcan --}}

</div>


{{-- Title Field --}}
<div class="form-group {{ $errors->has('title') ? ' invalid-feedback' : '' }}">
    <input type="text" name="title" class="form-control" placeholder="Nadpis ..."
        value="{{ old('title') ?? $post->title }}" required>
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

<x-dashboard.form-bar :cancel="url(URL::previous())"
    :submit="$post->exists ? 'Uložiť zmeny' : 'Vytvoriť článok'"
    :note="$post->exists ? 'Zmeny sa prejavia hneď po uložení.' : 'Článok sa vytvorí po kliknutí na tlačidlo.'" />

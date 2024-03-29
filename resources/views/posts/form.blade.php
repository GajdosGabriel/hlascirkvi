<div class="md:flex mb-6">

    <div class="form-category md:pr-2 {{ $errors->has('group_id') ? ' has-error' : '' }}">
        <label>Kategória</label>
        <select name="updaters" required class="form-control">
            <option value="" selected disabled>Vybrať kategóriu</option>
            @forelse(\App\Models\Updater::all() as $updater)
                @if ($updater->type == 'post')
                    <option class="option" value="{{ $updater->id }}"
                        @foreach ($post['updaters'] as $up)
                        @if ($up->pivot->updater_id == $updater->id)
                            selected
                        @endif @endforeach>
                        {{ $updater->title }}<br>
                @endif
            @empty
                žiadny tag
            @endforelse
        </select>
    </div>


    {{-- Video Link --}}
    <div class="form-category md:pr-2">
        <label>Video YouTube</label>
        <input type="text" name="video_id" value="{{ old('video_id') ?? $post->video_id }}" class="form-control"
            placeholder="Odkaz na video Youtube">
    </div>

    <div class="form-category md:pr-2">
        <label for="publishet1" class="whitespace-nowrap ">{{ trans('web.events_published_now') }}</label>
        <div class="flex space-x-5 form-control">

            <div class="flex space-x-2">
                <label for="publishet1" class="whitespace-nowrap  ">Teraz</label>
                <input type="radio" value="{{ date('Y-m-d H:i:s') }}" @if (is_string($post->published)) checked @endif
                    required id="publishet1" name="published">
            </div>

            <div class="flex space-x-2">
                <label for="publishet2" class="whitespace-nowrap ">Neskôr</label>
                <input type="radio" value="null"  @if ( $post->created_at and $post->published == null) checked @endif required
                    id="publishet2" name="published">
            </div>
        </div>
    </div>

    @can('admin')
        <div class="form-author">
            <label>Kanál</label>
            <select class="form-control" name="organization_id" required>
                <option value="" selected disabled>Autor</option>
                @if (auth()->user()->email == env('ADMIN_EMAIL'))
                    @foreach (\App\Models\Organization::orderBy('title', 'asc')->get() as $organization)
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
                @endif
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


{{-- Tags Field --}}
{{-- <div class="form-group"> --}}
{{-- @foreach (\App\Tag::all() as $tag) --}}
{{-- <label class="checkbox-inline"></label> --}}
{{-- {!! Form::checkbox('tags[]', $tag->id, null) !!} --}}
{{-- {{ $tag->tag }} --}}

{{-- @endforeach --}}
{{-- </div> --}}

{{-- Add post Field --}}
<div class="block">

    @foreach ($post->images as $image)
        <div style="max-width: 17rem; float: left; padding: 1rem "
            class="border-2 border-gray-300 rounded-md mx-2 shadow-lg hover:bg-gray-100">

            <event-picture-viewer :image="{{ $image }}" />

        </div>
    @endforeach
</div>

<div>

    <div class="form-group">
        <label>Obrázok</label>
        <input type="file" name="pictures[]" multiple class="form-control" accept="image/*">
    </div>

    <div class="flex justify-between">
        <a href="{{ url(URL::previous()) }}" class="btn">späť</a>
        <button type="submit" class="btn btn-primary">Uložiť</button>
    </div>

</div>

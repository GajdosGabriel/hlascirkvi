<div class="form-title">
    <label for="">{{ trans('web.events_title') }}</label>
    <input type="text" name="title" placeholder="Názov" value="{{ old('title') ?? $event->title }}"
        class="form-control" required>
</div>

<div class="form-textarea">
    <label for="">{{ trans('web.events_body') }}</label>
    <textarea rows="12" name="body" id="editor">{{ old('body') ?? $event->body }}</textarea>
</div>
<div style="display: flex">
    @foreach ($event->images as $image)
        <div style="max-width: 17rem; float: left; padding: 1rem">

            <event-picture-viewer :image="{{ $image }}" />

        </div>
    @endforeach

    {{-- Aby sa nezobrazoalo pri create ale len update --}}
    @if (empty($event))
        <div style="align-self: flex-end">
            <input type="checkbox" id="vizitka" name="vizitka" value="1">
            <label for="vizitka">Vytvoriť novú vizitku</label>
        </div>
    @endif
</div>

<h4 style="margin-top: 2rem">Príloha alebo obrázok</h4>
<div class="flex">
    <div class="form-group">
        <label id="picture"><strong>{{ trans('web.events_picture') }}</strong></label>
        <input id="picture" type="file" name="pictures[]" multiple placeholder="Obrázok"
            accept="image/*,application/pdf,application/doc,application/docx" class="form-control">
    </div>

</div>

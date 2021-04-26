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

            <event-picture-viewer inline-template>
                <div :class="{'fixed inset-0 bg-gray-600' : showButton}">
                    <div v-if="showButton" @click="showModal"
                        class="absolute right-0 border-gray-200 border-2 bg-gray-700 hover:bg-gray-500 rounded px-4 py-2 text-white font-semibold text-2xl cursor-pointer ">
                        Zrušiť x
                    </div>

                    <img class="rounded cursor-pointer" alt="{{ $image->title }}"
                    :class="{'h-full' : showButton}"
                        src="{{ url($image->OriginalImageUrl) }}" @click="showModal"
                    @click="showModal"
                  >
                </div>
            </event-picture-viewer>

            <div>
                <input type="checkbox" id="{{ $image->id }}" name="imageThumb" value="{{ $image->id }}">
                <label for="{{ $image->id }}">Zmazať</label>
            </div>
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
        <input id="picture" type="file" name="picture[]" multiple placeholder="Obrázok"
            accept="image/*,application/pdf,application/doc,application/docx" class="form-control">
    </div>

</div>

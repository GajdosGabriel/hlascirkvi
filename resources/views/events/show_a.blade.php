@component('components.pages.page_title')
    @slot('title')

        {{ $event->title }}

    @endslot

    @slot('title_right')

        @can('update', $event)
            <event-dropdown :post="{{ $event }}" />
        @endcan

    @endslot
@endcomponent

<div class="border-2 rounded-md border-gray-500 p-4 mb-6 shadow-md">
    <span>{{ ucfirst(localized_date('l', $event->start_at)) }}
        {{ localized_date('H.i', $event->start_at) }} hod.</span>
    <h5>{{ trans('web.events_city') }}
        {{ $event->village->fullname }}, {{ $event->street }}
        <br>
        {{-- {{ $event->start_at->format('d. m. Y')}}, o {{ $event->start_at->format('H.i') }} hod. --}}
        {{-- - {{ $event->end_at->format('d. m. Y')}}, o {{ $event->end_at->format('H.i') }} hod. --}}

        @if ($event->start_at->diffInDays($event->end_at))
            Od: {{ $event->start_at->format('d. m. Y') }}, {{ $event->start_at->format('H.i') }} hod.
            do: {{ $event->end_at->format('d. m. Y') }}, {{ $event->end_at->format('H.i') }} hod.
        @else
            Dňa: {{ $event->start_at->format('d. m. Y') }}, o {{ $event->start_at->format('H.i') }} -
            {{ $event->end_at->format('H.i') }} hod.
        @endif
    </h5>
</div>

<div class="">

    {!! $event->body !!}

    <p>Akciu zverejnil: {{ $event->organization->title }}</p>

    @if ($event->images()->whereType('img')->exists())
        @foreach ($event->images()->whereType('img')->get()
    as $image)
            {{-- <img alt="{{ $image->title }}" data-src="{{ url($image->OriginalImageUrl) }}"  class="lazyload rounded"  data-sizes="auto"> --}}

            <event-picture-viewer inline-template class="
                    my-6">
                <div :class="{'fixed inset-0 bg-gray-600' : open}">
                    <div v-if="open" @click="showModal"
                        class="absolute right-0 border-gray-200 border-2 bg-gray-700 hover:bg-gray-500 rounded px-4 py-2 text-white font-semibold text-2xl cursor-pointer ">
                        Zrušiť x
                    </div>
                    <img alt="{{ $image->title }}" src="{{ url($image->OriginalImageUrl) }}" @click="showModal"
                        class="rounded cursor-pointer">

                </div>
            </event-picture-viewer>

        @endforeach
    @endif




    {{-- // Facebook --}}

    {{-- <div --}}
    {{-- class="fb-like" --}}
    {{-- data-share="true" --}}
    {{-- data-width="450" --}}
    {{-- data-show-faces="true"> --}}
    {{-- </div> --}}

    <div>
        @foreach ($event->images as $image)
            @if ($image->mime == 'pdf' or $image->mime == 'doc' or $image->mime == 'docx' or $image->mime == 'txt')
                <a target="_blank" href="{{ route('events.download', [$image->url]) }}" alt="{{ $image->title }}">
                    <i class="far fa-file-pdf fa-4x"></i>
                    Príloha {{ $image->org_name }}
                </a>
            @endif
        @endforeach
    </div>

</div>



@if ($event->registration != 'no')
    <ticket-form inline-template>
        @include('events._form_ticket')
    </ticket-form>
@endif

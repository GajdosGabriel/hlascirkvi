<x-pages.page_title>
    <x-slot name="title">

        {{ $event->title }}

    </x-slot>

    <x-slot name="title_right">

        @can('update', $event)
            <dropdown-slot>
                @include('events._drop-down', ['item' => $event])
            </dropdown-slot>
        @endcan

    </x-slot>
</x-pages.page_title>

<div class="md:flex justify-between mb-10  items-start">


    <section class="border-2 rounded-md border-gray-500 p-4 mb-6 shadow-md">
        <time>{{ ucfirst(localized_date('l', $event->start_at)) }}
            {{ localized_date('H.i', $event->start_at) }} hod.</time>
        <h5>{{ trans('web.events_city') }}
            {{ $event->village->fullname }}, {{ $event->street }}
            <br>
            {{-- {{ $event->start_at->format('d. m. Y')}}, o {{ $event->start_at->format('H.i') }} hod. --}}
            {{-- - {{ $event->end_at->format('d. m. Y')}}, o {{ $event->end_at->format('H.i') }} hod. --}}


            <time>
                @if ($event->start_at->diffInDays($event->end_at))
                    Od: {{ $event->start_at->format('d. m. Y') }}, {{ $event->start_at->format('H.i') }} hod.
                    do: {{ $event->end_at->format('d. m. Y') }}, {{ $event->end_at->format('H.i') }} hod.
                @else
                    Dňa: {{ $event->start_at->format('d. m. Y') }}, o {{ $event->start_at->format('H.i') }} -
                    {{ $event->end_at->format('H.i') }} hod.
                @endif
            </time>

        </h5>

        @if ($event->online_link)
            <a href="{{ $event->online_link }}" target="_blank">
                <span class="font-bold text-red-500">Online prenos:</span> {{ $event->online_link }}
            </a>
        @endif
    </section>

    <div class="w-1/3">

        @if ($event->images()->whereType('img')->exists())
            @foreach ($event->images()->whereType('img')->get() as $image)
                {{-- <img alt="{{ $image->title }}" data-src="{{ url($image->OriginalImageUrl) }}"  class="lazyload rounded"  data-sizes="auto"> --}}

                <event-picture-viewer inline-template>
                    <div :class="{ 'fixed inset-0 bg-gray-600': open }">
                        <div v-if="open" @click="showModal"
                            class="absolute right-0 border-gray-200 border-2 bg-gray-700 hover:bg-gray-500 rounded px-4 py-2 text-white font-semibold text-2xl cursor-pointer ">
                            Zrušiť x
                        </div>
                        <img alt="{{ $image->title }}" src="{{ 'https://hlascirkvi.sk/' . $image->OriginalImageUrl }}"
                            @click="showModal" class="rounded cursor-pointer">

                    </div>
                </event-picture-viewer>
            @endforeach
        @endif
    </div>

</div>

<section>

    {!! $event->body !!}

    <p class="mb-6">Akciu zverejnil: {{ $event->organization->title }}</p>



</section>

<picture>

    @if ($event->images()->whereType('img')->exists())
        @foreach ($event->images()->whereType('img')->get() as $image)
            {{-- <img alt="{{ $image->title }}" data-src="{{ url($image->OriginalImageUrl) }}"  class="lazyload rounded"  data-sizes="auto"> --}}

            <event-picture-viewer inline-template>
                <div :class="{ 'fixed inset-0 bg-gray-600': open }">
                    <div v-if="open" @click="showModal"
                        class="absolute right-0 border-gray-200 border-2 bg-gray-700 hover:bg-gray-500 rounded px-4 py-2 text-white font-semibold text-2xl cursor-pointer ">
                        Zrušiť x
                    </div>
                    <img alt="{{ $image->title }}" src="{{ 'https://hlascirkvi.sk/' . $image->OriginalImageUrl }}"
                        @click="showModal" class="rounded cursor-pointer">

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

</picture>



@if ($event->registration != 'no')
    <ticket-form inline-template>
        @include('events._form_ticket')
    </ticket-form>
@endif

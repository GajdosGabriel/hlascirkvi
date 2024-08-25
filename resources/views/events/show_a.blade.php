<x-pages.page_title>
    <x-slot name="title">

        {{ $post->title }}

    </x-slot>

    <x-slot name="title_right">

        @can('update', $post)
            <dropdown-slot>
                @include('events._drop-down', ['item' => $post])
            </dropdown-slot>
        @endcan

    </x-slot>
</x-pages.page_title>

<div class="md:flex justify-between mb-10  items-start">


    <section class="border-2 rounded-md border-gray-500 p-4 mb-6 shadow-md">
        <time>{{ ucfirst(localized_date('l', $post->start_at)) }}
            {{ localized_date('H.i', $post->start_at) }} hod.</time>
        <h5>{{ trans('web.events_city') }}
            {{ $post->village->fullname }}, {{ $post->street }}
            <br>
            {{-- {{ $post->start_at->format('d. m. Y')}}, o {{ $post->start_at->format('H.i') }} hod. --}}
            {{-- - {{ $post->end_at->format('d. m. Y')}}, o {{ $post->end_at->format('H.i') }} hod. --}}


            <time>
                @if ($post->start_at->diffInDays($post->end_at))
                    Od: {{ $post->start_at->format('d. m. Y') }}, {{ $post->start_at->format('H.i') }} hod.
                    do: {{ $post->end_at->format('d. m. Y') }}, {{ $post->end_at->format('H.i') }} hod.
                @else
                    Dňa: {{ $post->start_at->format('d. m. Y') }}, o {{ $post->start_at->format('H.i') }} -
                    {{ $post->end_at->format('H.i') }} hod.
                @endif
            </time>

        </h5>

        @if ($post->online_link)
            <a href="{{ $post->online_link }}" target="_blank">
                <span class="font-bold text-red-500">Online prenos:</span> {{ $post->online_link }}
            </a>
        @endif
    </section>

    <div class="w-1/3">

        @if ($post->images()->whereType('img')->exists())
            @foreach ($post->images()->whereType('img')->get() as $image)
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

    {!! $post->body !!}

    <p class="mb-6">Akciu zverejnil: {{ $post->organization->title }}</p>



</section>

<picture>

    @if ($post->images()->whereType('img')->exists())
        @foreach ($post->images()->whereType('img')->get() as $image)
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
        @foreach ($post->images as $image)
            @if ($image->mime == 'pdf' or $image->mime == 'doc' or $image->mime == 'docx' or $image->mime == 'txt')
                <a target="_blank" href="{{ route('events.download', [$image->url]) }}" alt="{{ $image->title }}">
                    <i class="far fa-file-pdf fa-4x"></i>
                    Príloha {{ $image->org_name }}
                </a>
            @endif
        @endforeach
    </div>

</picture>



@if ($post->registration != 'no')
    <ticket-form inline-template>
        @include('events._form_ticket')
    </ticket-form>
@endif

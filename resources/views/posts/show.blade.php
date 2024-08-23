<div>
    <div class="">
        <div>
            @if (!$post->images)
                @forelse($post->images as $image)
                    <img class="rounded" style="width: 100%; margin-bottom: 2rem"
                        src="{{ url($image->originalImageUrl) }}">
                @empty
                @endforelse
            @endif
        </div>
        <div>

        </div>

    </div>
</div>

{{-- Video --}}
@if ($post->video_id)
    <div id="player">
        <iframe
            src="https://www.youtube.com/embed/{{ $post->video_id }}?origin=https://plyr.io&amp;iv_load_policy=3&amp;modestbranding=1&amp;playsinline=1&amp;showinfo=0&amp;rel=0&amp;enablejsapi=1"
            allowfullscreen allowtransparency allow="autoplay"></iframe>
    </div>
@else
    @forelse($post->images as $image)
        <img class="rounded" style="width: 100%; margin-bottom: 2rem" src="{{ url($image->OriginalImageUrl) }}">
    @empty
    @endforelse
@endif


<div class="mt-2 lg:flex justify-between border-b-2 border-gray-300">
    {{-- Title video --}}
    <div class="">
        <div class="flex flex-col items-start flex-shrink-0">
            <h1 class="text-lg font-semibold">{{ $post->title }}</h1>
            <div class="text-sm text-gray-400">
                <span> pridal: </span>
                <a href="{{ route('organizations.show', [$post->organization_id]) }}">
                    {{ $post->organization->title }}</a>
                |
                <time datetime="{{ $post->created_at }}">dňa: {{ $post->datetime }}</time>
                | zobrazení: {{ $post->count_view }}
            </div>
        </div>
    </div>

    {{-- Social button --}}
    <div class="flex space-x-2 mt-3">

        @if ($post->video_id)
            {{-- // Facebook --}}
            <div id="fb-root"></div>
            <div class="fb-share-button" data-href="{{ route('post.show', [$post->id, $post->slug]) }}"
                data-layout="button" data-size="small">
                <a target="_blank"
                    href="https://www.facebook.com/sharer/sharer.php?u=https%3A%2F%2Fdevelopers.facebook.com%2Fdocs%2Fplugins%2F&amp;src=sdkpreparse"
                    class="fb-xfbml-parse-ignore">
                    Zdieľať
                </a>
            </div>
        @endif

        @if (Session::get($post->slug) == $post->id)
            <a style="float: right" class="disabled" title="Video ste už doporúčali">Odporúčili
                ste</a>
        @else
            @if ($post->video_id)
                <favorite-post :post="{{ $post }}"></favorite-post>
                {{-- <recomend-video :data="{{ $post }}"></recomend-video> --}}
            @endif
        @endif


        @can('update', $post)
            <article-dropdown :post="{{ $post }}" />
        @endcan

    </div>

</div>

{{-- Body section --}}
<div class="md:grid grid-cols-12 gap-4 mt-4">

    <organization-page-header :organization="{{ $post->organization }}">
    </organization-page-header>

    <div class="col-span-8">


        <div>{!! $post->body !!}</div>

        @if (!$post->video_id)
            {{-- // Facebook --}}
            <div>
                <div class="fb-like" data-share="true" data-width="350" data-show-faces="true">
                </div>
            </div>
        @endif

        @auth
            @include('bigthink._form')
        @endauth

        <comments-post :post="{{ $post }}"></comments-post>
    </div>

    {{-- Body plánované akcie --}}
    <div class="col-span-4 mb-4">

        <x-events.modul-organization-events :organization="$post->organization" :post="$post" />

    </div>

</div>

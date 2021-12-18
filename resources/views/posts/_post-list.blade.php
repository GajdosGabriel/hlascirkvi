<div class="flex mb-4  border-2 border-gray-200 rounded-md shadow-md">

    <div style="max-height: 12rem; max-width: 11rem; overflow: hidden; position: relative">
        @if ($post->favorites()->count())
            <div class="absolute bottom-0 right-0 bg-red-600 p-1 rounded-sm text-xs text-gray-200">
                Doporúčené
            </div>
        @endif
        <a href="{{ route('post.show', [$post->id, $post->slug]) }}">
            @include('posts.image')
        </a>
    </div>

    <div class=" w-full mt-2">
        <div class="flex justify-between">
            <a href="{{ route('post.show', [$post->id, $post->slug]) }}">
                <h6 class="pb-2 px-2 font-semibold mb-10 block" title="{{ $post->title }}">{{ $post->title }}</h6>
            </a>

            @can('update', $post)
                <article-dropdown :post="{{ $post }}" />
            @endcan
        </div>

        <div class="text-gray-500 px-2 italic">
            <a href="{{ route('organizations.show', [$post->organization->id]) }}">
                {{ $post->organization->title }}
            </a>
            <time datetime="{{ $post->created_at }}">{{ $post->createdAtHuman }}</time>
        </div>
    </div>

</div>

<div class="border-2 border-gray-400 rounded-md shadow-md relative">

    <div style="max-height: 11rem; overflow: hidden; position: relative">
        @if($post->favorites()->count() > 0)
            <div class="absolute bottom-0 right-0 bg-red-600 p-1 rounded-sm text-xs text-gray-200">
                Doporúčené
            </div>
        @endif
        <a href="{{ route('post.show', [$post->id, $post->slug]) }}">
            @include('posts.image')
        </a>
    </div>

    <div class="px-2 font-semibold mb-12">
        <a href="{{ route('post.show', [$post->id, $post->slug]) }}">
            <h6 class="text-md" title="{{ $post->title }}">{{ Str::limit($post->title, 48) }}</h6>
        </a>
    </div>


    <div class="text-gray-500 text-sm px-2 italic absolute bottom-0 flex flex-col">
        <a href="{{ route('organization.show', [$post->organization->id, $post->organization->slug]) }}">
            {{ $post->organization->title }}
        </a>
        <time datetime="{{ $post->created_at }}">{{ $post->created_at->diffForHumans() }}</time>
    </div>

</div>

<div class="border-2 border-gray-400 rounded-md shadow-md relative text-xs md:text-sm">

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

    <a href="{{ route('post.show', [$post->id, $post->slug]) }}">
        <h6 class="pb-2 px-2 font-semibold mb-10" title="{{ $post->title }}">{{ Str::limit($post->title, 48) }}</h6>
    </a>


    <div class="text-gray-500 px-2 italic absolute bottom-0 flex flex-col text-xs md:text-sm">
        <a href="{{ route('organization.show', [$post->organization->id, $post->organization->slug]) }}">
            {{ $post->organization_name }}
        </a>
        <time datetime="{{ $post->created_at }}">{{ $post->created_at->diffForHumans() }}</time>
    </div>

</div>

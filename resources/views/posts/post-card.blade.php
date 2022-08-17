<div class="border-2 border-gray-400 rounded-md shadow-md relative text-xs md:text-sm">

    <div style="max-height: 11rem; overflow: hidden; position: relative">
        @if ($post->favorites()->count())
            <div class="absolute bottom-0 right-0 bg-red-600 p-1 rounded-sm text-xs text-gray-200">
                Doporúčené
            </div>
        @endif

        @if ($post->video_duration())
            <div class="absolute bottom-0 right-0 bg-gray-700 p-1 rounded-sm text-xs text-gray-200">
                {{ $post->video_duration() }}
            </div>
        @endif

        @if ($post->comments()->count())
            <div class="absolute bottom-0 left-0 bg-red-600 p-1 rounded-sm text-xs text-gray-200 flex">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z"
                        clip-rule="evenodd" />
                </svg>
                {{ $post->comments()->count() }}
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
        <a href="{{ route('organizations.show', [$post->organization->id]) }}">
            {{ $post->organization->title }}
        </a>
        <time datetime="{{ $post->created_at }}">{{ $post->createdAtHuman }}</time>
    </div>

</div>

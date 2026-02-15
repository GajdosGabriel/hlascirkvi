<div class="border-2 border-gray-400 rounded-md shadow-md relative text-xs md:text-sm flex flex-col h-full">
    @php
        $postUrl = route('post.show', [$post->id, $post->slug]);
        $organizationUrl = route('organizations.show', [$post->organization->id]);
        $hasFavorites = ($post->favorites_count ?? null) ? $post->favorites_count > 0 : $post->favorites()->exists();
        $hasComments = ($post->comments_count ?? null) ? $post->comments_count > 0 : $post->comments()->exists();
    @endphp

    <!-- Obrazok a overlay prvky -->
    <div class="relative">
        @if ($hasFavorites)
            <div class="absolute top-0 right-0 bg-red-600 p-1 rounded-sm text-xs text-gray-200 z-10">
                Doporucene
            </div>
        @endif

        @if ($post->video_duration)
            <div class="absolute bottom-0 right-0 bg-gray-700 p-1 rounded-sm text-xs text-gray-200 z-10">
                {{ $post->video_duration }}
            </div>
        @endif

        @if ($hasComments)
            <div class="absolute bottom-0 left-0 bg-red-600 p-1 rounded-sm text-xs text-gray-200 flex z-10">
                <!-- SVG a pocet komentarov -->
            </div>
        @endif

        <a href="{{ $postUrl }}">
            @include('posts.image')
        </a>
    </div>

    <!-- Nadpis -->
    <a href="{{ $postUrl }}" class="flex-grow">
        <h6 class="pb-2 px-2 font-semibold" title="{{ $post->title }}">
            {{ Str::limit($post->title, 48) }}
        </h6>
    </a>

    <!-- Datum a organizacia -->
    <div class="text-gray-500 px-2 italic flex flex-col text-xs md:text-sm w-full mt-auto pb-2">
        <a href="{{ $organizationUrl }}" class="hover:underline">
            {{ $post->organization->title }}
        </a>
        <time datetime="{{ $post->created_at->toIso8601String() }}">{{ $post->dateForHumans }}</time>

        @if (Route::is('admin.buffer.index'))
            <post-publish-buttons :post="{{ $post }}" />
        @endif
    </div>
</div>

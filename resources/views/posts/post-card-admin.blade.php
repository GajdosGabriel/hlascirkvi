<div class="border-2 border-gray-400 rounded-md hover:shadow-md shadow-sm text-xs md:text-sm flex my-4">

    <div style="max-width: 160px;" class="p-2">
        @if ($post->favorites()->count())
            <div class="absolute bottom-0 right-0 bg-red-600 p-1 rounded-sm text-xs text-gray-200">
                Doporúčené
            </div>
        @endif

        @if ($post->video_duration)
            <div class="absolute bottom-0 right-0 bg-gray-700 p-1 rounded-sm text-xs text-gray-200">
                {{ $post->video_duration }}
            </div>
        @endif

        <div style="height: 81px; overflow: hidden;">
            <a href="{{ route('post.show', [$post->id, $post->slug]) }}">
                @include('posts.image')
            </a>
        </div>
    </div>



    <div class="w-full p-2 flex flex-col">

        <div class="flex justify-between">

            <h6 class="font-semibold" title="{{ $post->title }}">
                <a href="{{ route('post.show', [$post->id, $post->slug]) }}">
                    {{ Str::limit($post->title, 48) }}
                </a>
            </h6>


            @can('update', $post)
                <dropdown-slot>

                    <ul class="dropdown-menu z-50">
                        <a href={{ route('organization.post.edit', [$post->organization_id, $post->id]) }}>
                            <li class="dropdown-item">upraviť</li>
                        </a>

                        <li class="dropdown-item">
                            <form action="{{ route('organization.post.destroy', [$post->organization_id, $post->id]) }}"
                                method="post">
                                @csrf @method('DELETE')
                                <button>zmazať</button>
                            </form>
                        </li>

                        <li class="dropdown-item whitespace-nowrap">

                            <form action="{{ route('postSupport.update', [$post->id]) }}" method="post">
                                @csrf @method('PUT')
                                <button>Do buffer</button>
                            </form>
                        </li>
                    </ul>
                </dropdown-slot>
            @endcan
        </div>



        <div class="flex justify-between  mt-auto">
            <div class="flex space-x-5">

                <time datetime="{{ $post->created_at }}">{{ $post->dateForHumans() }}</time>

                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z"
                            clip-rule="evenodd" />
                    </svg>
                    {{ $post->comments()->count() }}
                </div>

                <div>Zobrazenia {{ $post->count_view }}</div>
            </div>
            <div>
                @if (!$post->published)
                    <span
                        class="px-1 text-xs bg-red-500 text-gray-100 rounded border-2 border-red-700">Nepublikované</span>
                @endif
                <span class="label-primary">label</span>
                <span class="label-success">label</span>
            </div>
        </div>
    </div>


</div>

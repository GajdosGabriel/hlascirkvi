

<section class="border-2 border-gray-500 rounded-sm mb-8">

    <div class="">
        <div class="text-white cursor-pointer p-3 text-base" style="background: #6c6c6c">
            <div class="flex justify-between items-center cursor-pointer">
                <h4>Kresťanské osobnosti</h4>
                <svg class="h-5 w-5 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                </svg>

            </div>
        </div>

        <ul class="p-3">
            @forelse( $users as $user)
                <li class="flex justify-between">
                    <a href="{{ route('organization.show', [$user->id, $user->slug]) }}">{{ $user->title }}</a>
                    <span>

                ({{ $user->posts_count }})

                    {{--@can('admin')--}}
                        {{--<a href="{{ route('youtube.searchAndSaveUser', [$user->id, $user->slug]) }}">--}}
                        {{--<i title="Hľadať nové videa" class="fas fa-search"></i>--}}
                        {{--</a>--}}
                        {{--@endcan--}}
                </span>
                </li>
            @empty
                bez záznamu
            @endforelse
        </ul>
    </div>


</section>




<x-cards.card :title="'Kresťanské osobnosti'" :icon="'components.icons.users'">
    <ul class="p-3">
        @forelse( $users as $user)
            <li class="flex justify-between hover:bg-gray-100">
                <a href="{{ route('organizations.show', [$user->id]) }}">{{ $user->title }}</a>
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

</x-cards.card>




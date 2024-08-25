<x-cards.card :title="'Kresťanské osobnosti'" :icon="'components.icons.users'">
    <ul class="p-3">
        @forelse($users as $user)
            <a href="{{ route('public.organizations.show', [$user->id]) }}">
                <li class="flex justify-between hover:bg-gray-100">

                    {{ $user->title }}
                    <span>

                        ({{ $user->posts_count }})
                        {{-- @can('admin') --}}
                        {{-- <a href="{{ route('youtube.searchAndSaveUser', [$user->id, $user->slug]) }}"> --}}
                        {{-- <i title="Hľadať nové videa" class="fas fa-search"></i> --}}
                        {{-- </a> --}}
                        {{-- @endcan --}}
                    </span>

                </li>
            </a>
        @empty
            bez záznamu
        @endforelse
    </ul>

</x-cards.card>

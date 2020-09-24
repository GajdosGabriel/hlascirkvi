
<div style="margin-bottom: 3rem">
    <h5 style="font-weight: bold">Kresťanské osobnosti</h5>
    <ul>
        @forelse( $users as $user)
            <li class="level">
                <a href="{{ route('organization.show', [$user->id, $user->slug]) }}">{{ $user->title }}</a>
                <span style="margin-left: 5rem">

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


<section class="card">
    <header class="card_header">Kresťanské osobnosti</header>
    <div class="card_body">

        @forelse( $users as $k => $v)
            <ul>

                @foreach( $v as $value)

                    @if ($loop->first)

                        <li class="flex justify-between">
                            <a href="?posts={{ $value->organization->id }}">{{ $value->organization->title }}</a>
                            <span style="margin-left: 5rem">

                    ({{ $v->count() }})
                    {{--@can('admin')--}}
                                {{--<a href="{{ route('videos.searchUserVideo', [$value->organization->id]) }}">--}}
                                {{--priesk--}}
                                {{--</a>--}}
                                {{--@endcan--}}

                                {{--({{ $user->posts()->count() }})--}}

                                {{--@can('admin')--}}
                                {{--<a href="{{ route('youtube.searchAndSaveUser', [$value->organization->id, $value->organization->slug]) }}">--}}
                                {{--<i title="Hľadať nové videa" class="fas fa-search"></i>--}}
                                {{--</a>--}}
                                {{--@endcan--}}
                </span>
                        </li>
                    @endif
                @endforeach
            </ul>
        @empty
            bez záznamu
        @endforelse
    </div>

</section>


<div style="margin-bottom: 3rem">
    <h5 style="font-weight: bold">Najnovšie príspevky</h5>
    @forelse( $organizations as $k => $organizations)
        @forelse( $organizations as $post)
            <ul>
                <li class="flex">
                    <a title="{{ $post->organization->title }}" href="{{ route('organization.posts', [$post->organization->id, $post->organization->slug]) }}">
                        {{ str_limit($post->organization->title, 23) }}
                    </a>
                     {{--<div>dnes</div>--}}
                     <span style="font-size: 60%; color: whitesmoke; background: red; padding: 0rem .8rem; border-radius: 1rem">{{ $post->created_at->diffForHumans() }}</span>
                </li>
            </ul>
            @break
        @empty
            bez záznamu
        @endforelse
    @empty
        bez záznamu
    @endforelse
</div>

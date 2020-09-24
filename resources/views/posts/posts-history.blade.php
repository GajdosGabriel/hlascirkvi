@if(Session::get('postsHistory'))
<div style="margin-bottom: 3rem">
    <h5 style="font-weight: bold">Vaša história</h5>
    @forelse( Session::get('postsHistory') as $post)
        <ul>
            <li style="font-size: 81%; color: #989898" class="level">
                <a href="{{ route('post.show', [$post->id, $post->slug]) }}">{{ $post->title }}</a>
                <span style="margin-left: 5rem">
               {{--datum--}}
                </span>
            </li>
        </ul>
    @empty
        Ste tu prvykrát
    @endforelse
</div>

@endif
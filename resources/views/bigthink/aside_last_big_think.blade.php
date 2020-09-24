<section class="event">

    <div class="card">

        <div class="card-header">
            <span>Najnovšie myšlienky</span>
            <i class="far fa-lightbulb"></i>
        </div>

        <div class="card-body">
            @forelse( $bigThings as $bigThing)
                <div style="margin-bottom: .5rem">
                    <a href="{{ route('post.show', [$bigThing->post->id, $bigThing->post->slug]) }}">
                    <span style="font-weight: bold" title="{{ $bigThing->post->title }}">{{ Str::limit($bigThing->post->title, 30, ' ...') }}</span><br>
                        <span>{{ Str::limit( $bigThing->body, 70, ' ...' ) }}</span>
                    </a>
                </div>
            @empty
            @endforelse
        </div>
    </div>

</section>


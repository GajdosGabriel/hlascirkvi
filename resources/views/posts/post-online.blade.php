@if ($post->video_id)

    <div class="aspect-w-16 aspect-h-9">
        <iframe  width="640" height="360" src="//www.youtube.com/embed/{{ $post->video_id }}?rel=0;modestbranding=1" frameborder="0" allowfullscreen></iframe>
    </div>

@endif



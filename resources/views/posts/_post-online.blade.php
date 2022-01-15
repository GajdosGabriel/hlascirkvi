@if ($post->video_id)

    <div class="aspect-w-16 aspect-h-9">
        <iframe src="//www.youtube.com/embed/{{ $post->video_id }}?rel=0;modestbranding=1" frameborder="0"
            allowfullscreen></iframe>
    </div>

@else

    <img class="rounded" style="width: 100%; margin-bottom: 2rem" src="{{ url($post->thumb_image) }}">



@endif

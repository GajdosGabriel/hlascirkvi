@if ($post->video_id)

    <div class="aspect-w-16 aspect-h-9">
        <iframe src="https://www.youtube-nocookie.com/embed/{{ $post->video_id }}?rel=0&amp;modestbranding=1" frameborder="0"
            loading="lazy" title="{{ $post->title }}" allowfullscreen></iframe>
    </div>

@else

    <img class="rounded" style="width: 100%; margin-bottom: 2rem" src="{{ url($post->thumb_image) }}">



@endif

@if ($post->video_id)

        <div class="w-2/3">
            <iframe  width="640" height="360" src="//www.youtube.com/embed/{{ $post->video_id }}?rel=0;modestbranding=1" frameborder="0" allowfullscreen></iframe>
        </div>

@endif



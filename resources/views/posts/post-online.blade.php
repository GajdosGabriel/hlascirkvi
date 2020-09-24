@if ($post->video_id)

        <div class="video-container">
            <iframe  width="640" height="360" src="//www.youtube.com/embed/{{ $post->video_id }}?rel=0;modestbranding=1" frameborder="0" allowfullscreen></iframe>
        </div>

@endif





{{--@if ($post->video_id)--}}
{{--    <post-counter inline-template post="{{ $post->video_id }}" idpost="{{ $post->id }}">--}}

{{--        <div class="video-container">--}}
{{--            @forelse($post->images as $image)--}}
{{--                <img @click="sendPostShow" v-show="! showvideo" style="width: 100%; cursor: pointer" alt="{{ $post->organization->title }} / {{ $post->title }}" src="http://img.youtube.com/vi_webp/{{ $post->video_id }}/mqdefault.webp">--}}
{{--                @break--}}
{{--            @empty--}}
{{--                @if(isset($post->user->avatar))--}}
{{--                    <img class="crop-image" src="{{ Storage::url($post->user->userPictureUrl() ) }}"   alt="{{ $post->organization->title }}">--}}
{{--                @else--}}
{{--                    <img class="crop-image" src="{{ asset('images/foto.jpg') }}" alt="{{ $post->organization->title }}">--}}
{{--                @endif--}}
{{--            @endforelse--}}

{{--            --}}{{--<iframe  width="640" height="360" src="//www.youtube.com/embed/{{ $post->video_id }}?rel=0;modestbranding=1" frameborder="0" allowfullscreen></iframe>--}}
{{--            <iframe v-if="showvideo" width="640" height="360" :src="linksrc" frameborder="0"  allowfullscreen></iframe>--}}
{{--        </div>--}}

{{--    </post-counter>--}}

{{--@endif--}}

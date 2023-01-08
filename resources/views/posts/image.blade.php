@if(!empty($post->images))
    @forelse($post->images as $image)
        <img alt="{{ $post->organization->title }} / {{ $post->title }}" data-src="{{ url($image->ThumbImageUrl) }}"  class="lazyload"  data-sizes="auto">
        @break
        @empty

        @if(isset($post->user->avatar))
            <img data-src="{{ Storage::url($post->user->userPictureUrl() ) }}" alt="{{ $post->organization->title }}"  class="lazyload"  data-sizes="auto">
        @else
            <img data-src="{{ asset('images/foto.jpg') }}" alt="{{ $post->organization->title }}"  class="lazyload w-full"  data-sizes="auto">
        @endif
    @endforelse
@endif


@forelse($post->images as $image)
    <img alt="{{ $post->organization->title }} / {{ $post->title }}" src="{{ url($image->ThumbImageUrl) }}"
        style="width: 100px; float: left; margin-right: 10px ">
@break
@empty
    @if (isset($post->user->avatar))
        <img src="{{ Storage::url($post->user->userPictureUrl()) }}" alt="{{ $post->organization->title }}"
            style="width: 100px; float: left; margin-right: 10px ">
    @else
        <img src="{{ asset('images/foto.jpg') }}" alt="{{ $post->organization->title }}"
            style="width: 100px; float: left; margin-right: 10px ">
    @endif
    @endforelse

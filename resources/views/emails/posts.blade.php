<div style="padding: 4px; background: silver; text-align: center;">
    <p style="font-weight: 800; font-size: larger;">
        Hlas cirkvi.sk
    </p>
</div>

<div style="text-align: center; margin-bottom: 20px; background: rgb(224, 223, 223); padding:20px; width:100% ">


    <div style=" padding:2px; max-width: 700px; margin:auto; background: white ">
        <h1>Najlepšie príspevky za mesiac</h1>
        <p>Zoradené podľa počtu sledovaní.</p>

    </div>

    <div>
        @forelse($posts as $post)

            <div style=" background: white; padding-bottom:2px; max-width: 700px; margin:auto; border-style: solid;
        border-color: rgb(23, 6, 117); margin-bottom: 10px ">

                <div
                    style="font-size: 120%; color: rgb(119, 117, 117);  padding:10px; background: rgb(23, 6, 117); color:antiquewhite; margin-bottom:10px ">
                    {{ $loop->index + 1 }}. miesto</div>

                @forelse($post->images as $image)
                    <img alt="{{ $post->organization->title }} / {{ $post->title }}"
                        src="{{ url($image->ThumbImageUrl) }}" style="min-width: 260px">
                @break
            @empty
                @if (isset($post->user->avatar))
                    <img src="{{ Storage::url($post->user->userPictureUrl()) }}"
                        alt="{{ $post->organization->title }}" style="min-width: 260px">
                @else
                    <img src="{{ asset('images/foto.jpg') }}" alt="{{ $post->organization->title }}"
                        style="min-width: 260px">
                @endif
            </div>
        @endforelse


        <div style="margin-top: -.8rem">
            <a style="text-decoration: none" href="{{ route('post.show', [$post->id, $post->slug]) }}">
                <h2 style="font-size: 130%" title="{{ $post->title }}">
                    {{ $post->title }}
                </h2>
            </a>
        </div>

        <div style="margin-top: -10px; font-size: 90%; margin-bottom:20px ">Autor: {{ $post->organization->title }}
        </div>

    </div>
@empty
    bez záznamu
    @endforelse




   

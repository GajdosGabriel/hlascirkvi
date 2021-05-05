<div style="padding: 4px; background: silver; text-align: center;">
    <p style="font-weight: 800; font-size: larger;">
        Hlas cirkvi.sk
    </p>
</div>

<div style="margin-bottom: 20px; background: rgb(224, 223, 223); padding:20px; width:100% ">


    <div style="text-align: center;  padding:2px; max-width: 700px; margin:auto; background: white ">
        <h1>Najlepšie príspevky za mesiac</h1>
        <p>Zoradené podľa počtu sledovaní.</p>

    </div>

    <div style=" background: white; padding-bottom:2px; max-width: 700px; margin:auto; border-style: solid;
    border-color: rgb(23, 6, 117); margin-bottom: 10px;">

        {{-- Image --}}
        @forelse($posts as $post)
            <div style=" padding:10px  ">

                @include('emails.image_post')

                <div style="margin-top: -.8rem">
                    <a style="text-decoration: none" href="{{ route('post.show', [$post->id, $post->slug]) }}">
                        <h2 style="font-size: 100%" title="{{ $post->title }}">
                            {{ $post->title }}
                        </h2>
                    </a>
                </div>

                <div style="margin-top: -10px; font-size: 90%; margin-bottom:20px ">Autor:
                    {{ $post->organization->title }}
                </div>

            </div>
        @empty
            bez záznamu
        @endforelse


    </div>
</div>

<div style="padding: 4px; background: silver; text-align: center;">
    <p style="font-weight: 800; font-size: larger;">
        HlasCirkvi.sk
    </p>
</div>

<div style="margin-bottom: 20px; background: rgb(224, 223, 223); width:100% ">


    <div style="text-align: center;  padding:2px; max-width: 700px; margin:auto; background: white ">
        <h1>Novinky z kresťanského portálu</h1>
        <h2>Mesačný prehľad</h2>
    </div>

{{-- Posts --}}
    <div style="background: white; padding-bottom:2px; max-width: 700px; margin:auto; border-bottom: 5px solid rgb(133, 130, 130);
                border-color: rgb(23, 6, 117); margin-bottom: 10px;">

        <h2 style="padding-left:10px; padding-top:10px ">Najsledovanejšie videa</h2>
        {{-- Image --}}
        @forelse($posts as $post)
            <div style=" padding:10px  ">

                @include('emails.component.image_post')

                <div style="margin-top: -.8rem">
                    <a style="text-decoration: none" href="{{ route('post.show', [$post->id, $post->slug]) }}">
                        <h2 style="font-size: 100%" title="{{ $post->title }}">
                            {{ $post->title }}
                        </h2>
                    </a>
                </div>

                <div style="margin-top: -10px; font-size: 90%; margin-bottom:20px">
                    Autor: {{ $post->organization->title }}
                </div>

            </div>
        @empty
            bez záznamu
        @endforelse

    </div>



    {{-- Events --}}
    <div style="background: white; padding-bottom:2px; max-width: 700px; margin:auto; border-bottom: 5px solid rgb(133, 130, 130);
    border-color: rgb(23, 6, 117); margin-bottom: 10px;">

        <h2 style="padding-left:10px; padding-top:10px "> Pozvánky na podujatia</h2>
        {{-- Image --}}
        @forelse($events as $event)

            <div style=" padding:10px ; margin-bottom: 20px; overflow: hidden; ">


                @include('emails.component.image_event')


                <div style="">
                    <a style="text-decoration: none" href="{{ route('post.show', [$event->id, $event->slug]) }}">
                        <h2 style="font-size: 100%" title="{{ $event->title }}">
                            {{ $event->title }}
                        </h2>
                    </a>
                </div>

                <span class="">{{ $event->organization->city }} </span>
                <span class="">{{ $event->start_at->diffForHumans() }}</span>,
                <span class="">Miesto: <span style="font-weight: 600"> {{ $event->village->district->name }}</span></span>,
                <span class="">Pridal: {{ $event->organization->title }}</span>

            </div>
        @empty
            bez záznamu
        @endforelse


    </div>
</div>

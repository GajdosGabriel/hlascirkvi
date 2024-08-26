<div style="padding: 4px; background: silver; text-align: center;">
    <p style="font-weight: 800; font-size: larger;">
        HlasCirkvi.sk
    </p>
</div>

<div style="text-align: center;  padding:2px; margin:auto; background: white ">
    <h2>Novinky z kresťanského portálu</h2>
    <h2>Pozvánka na akciu</h2>
</div>

<div style="margin-bottom: 20px; background: rgb(224, 223, 223); width:100%; padding:10px  ">

    {{-- Events --}}
    <div
        style="background: white; padding-bottom:2px; max-width: 700px; margin:auto; border-bottom: 5px solid rgb(133, 130, 130);
    border-color: rgb(23, 6, 117); margin-bottom: 10px;">

        <h2 style="padding-left:10px; padding-top:10px "> <span style="color:grey"> Online stretnutie:</span> 
            <a style="text-decoration: none" href="{{ $event->url['show'] }}">
            {{ $event->title }}
            </a>
        </h2>
        {{-- Image --}}

        <div style=" padding:10px ; margin-bottom: 20px; overflow: hidden; ">

            @include('emails.component.image_event')


            <div style="">
                <a style="text-decoration: none" href="{{ $event->url['show'] }}">
                    <h2 style="font-size: 130%" title="{{ $event->title }}">
                        {{ $event->title }}
                    </h2>
                </a>
            </div>

            <div style="margin-bottom: 10px">Kde: {{ $event->organization->city }} </div>
            <div style="margin-bottom: 10px">začiatok: {{ $event->start_at->diffForHumans() }}</div>
            <div style="margin-bottom: 10px">Miesto: <span style="font-weight: 600">
                    {{ $event->village->district->name }}</span></div>
            <div style="margin-bottom: 10px">Pridal: {{ $event->organization->title }}</div>

        </div>

        <h2 style="padding-left:10px; padding-top:10px "> <span style="color:grey">Viac informácií:</span> 
            <a style="text-decoration: none" href="{{ $event->url['show'] }}">
           Tu
            </a>
        </h2>

    </div>
</div>


<div style="text-align: center;  padding:2px; margin:auto; background: white ">
    <p>Novinky z kresťanského portálu</p>
    <p>Odhlásenie</p>
</div>


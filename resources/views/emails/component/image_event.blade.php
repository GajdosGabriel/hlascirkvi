
    <a href="{{ $event->routeShow() }}">
        @if ($event->images()->whereType('img')->exists())
            @foreach($event->images()->whereType('img')->get() as $image)
                <img src="{{ url($image->ThumbImageUrl) }}"
                     alt="{{ $event->title }}" style="width: 400px; float: left; margin-right: 10px; max-height: 90px; ">
                @break
            @endforeach
        @else
            @foreach($event->images()->whereType('card')->get() as $image)
                <img src="{{ url($image->ThumbImageUrl) }}"
                     alt="{{ $event->title }}" style="width: 400px; float: left; margin-right: 10px ">
                @break
            @endforeach
        @endif
    </a>

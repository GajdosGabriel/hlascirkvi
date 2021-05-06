
    <a href="{{ $event->url }}">
        @if ($event->images()->whereType('img')->exists())
            @foreach($event->images()->whereType('img')->get() as $image)
                <img src="{{ url($image->ThumbImageUrl) }}"
                     alt="{{ $event->title }}" style="width: 100px; float: left; margin-right: 10px;  ">
                @break
            @endforeach
        @else
            @foreach($event->images()->whereType('card')->get() as $image)
                <img src="{{ url($image->ThumbImageUrl) }}"
                     alt="{{ $event->title }}" style="width: 100px; float: left; margin-right: 10px ">
                @break
            @endforeach
        @endif
    </a>

{{-- Currently events --}}
<div class="text-sm text-gray-700 p-2">
    <h4 class="font-semibold pb-2">Práve prebiehajúce akcie</h4>
    <div class="flex">
        @forelse($currentlyEvents as $event)
            <div class="flex flex-col p-3">
                <a href="{{ $event->url }}">
                    @if ($event->imagethumb)
                        <img data-src="{{ url($event->imagethumb) }}" class="lazyload rounded w-22" data-sizes="auto"
                            alt="{{ $event->title }}">

                    @else

                        <img data-src="{{ url($event->imagecard) }}" class="lazyload rounded w-full" data-sizes="auto"
                            alt="{{ $event->title }}">

                    @endif
                </a>


                <div class="flex mb-2">
                    <a class="hover:text-gray-900" href="{{ $event->url }}">{{ $event->title }}</a>
                </div>
            </div>
        @empty
            Aktuálne neprebieha žiadna akcia
        @endforelse
    </div>
</div>

</div>

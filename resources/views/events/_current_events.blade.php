{{-- Currently events --}}
<div class="text-sm text-gray-700 border-gray-300 border-2 rounded-md m-4">
    <h4 class="font-semibold text-2xl rounded-t-md bg-yellow-500 shadow-md mb-4 p-2">Práve prebiehajúce akcie</h4>
    <div class="grid lg:grid-cols-12 md:grid-cols-4 grid-cols-2 gap-4 p-2">
        @forelse($currentlyEvents as $event)
            <div class="flex flex-col hover:bg-gray-100 p-2 relative">
                <div class="inset-0 overflow-hidden max-h-16">
                    <a href="{{ $event->url }}">
                        @if ($event->imagethumb)
                            <img data-src="{{ url($event->imagethumb) }}" class="lazyload rounded w-full"
                                data-sizes="auto" alt="{{ $event->title }}">

                        @else

                            <img data-src="{{ url($event->imagecard) }}" class="lazyload rounded w-full"
                                data-sizes="auto" alt="{{ $event->title }}">

                        @endif
                    </a>
                </div>


                <div class="flex mb-2">
                    <a class="hover:text-gray-900 font-semibold " href="{{ $event->url }}">
                        {{ Str::limit($event->title, 36, $end = ' ...') }}
                    </a>
                </div>

                <div class="mb-4">

                </div>

                <div class=" text-xs absolute bottom-0">
                    {{ $event->village->fullname }}<br/>
                   <span class="text-red-600">končí: {{ $event->end_at->diffForHumans() }}</span>
                </div>

            </div>
        @empty
            Aktuálne neprebieha žiadna akcia
        @endforelse
    </div>
</div>

</div>

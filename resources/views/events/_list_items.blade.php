<p class="font-semibold mb-3">
    {{ ucfirst(localized_date('l', $event->start_at)) }}
    {{ localized_date('d. m. Y', $event->start_at) }}
</p>
<div class="md:grid grid-cols-8 gap-4 mb-10">
    <div class="col-span-1 overflow-hidden">
        <a href="{{ $event->url }}">
            @if ($event->imagethumb)
                <img data-src="{{ url($event->imagethumb) }}" class="lazyload rounded w-full" data-sizes="auto"
                    alt="{{ $event->title }}">
            @else
                <img data-src="{{ url($event->imagecard) }}" class="lazyload rounded w-full" data-sizes="auto"
                    alt="{{ $event->title }}">
            @endif
        </a>
    </div>

    <div class="col-span-5">
        <div class="post-header">
            <div class="title flex justify-between">
                <h5 class="text-lg font-semibold"><a href="{{ $event->url }}">{{ $event->title }}</a></h5>

                @can('update', $event)
                    <event-dropdown :post="{{ $event }}" />
                @endcan

            </div>

            <div class="text-gray-600">
                {{ html_entity_decode(strip_tags(\Illuminate\Support\Str::limit($event->body, 200))) }}
            </div>
        </div>


        {{-- ------- Admin panel ----------------- --}}
        @can('update', $event)
          <x-events.thumbs.thumbadmin :event="$event" />
        @endcan

    </div>
    <div class="col-span-2 flex flex-col">
        {{-- <strong class="pull-right">{{ localized_date('l', $event->dateStart) }}</strong><br> --}}
        {{-- <span class="">{{ $event->organization->city }} </span> --}}
        <a href="?district={{ $event->village->district->id }}">
            <span class="">{{ $event->village->district->name }}</span>
        </a>
        <span class="">{{ $event->start_at->diffForHumans() }}</span>
        <span class="">Pridal: {{ $event->organization->title }}</span>
    </div>
</div>

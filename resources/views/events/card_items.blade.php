<div class="font-semibold mb-3 w-fit border-b-4 border-gray-400 ">
    {{ ucfirst(localized_date('l', $event->start_at)) }}
    {{ localized_date('d. m. Y', $event->start_at) }}
</div>

<section class="md:grid grid-cols-8 gap-4 mb-10 hover:bg-gray-50 p-2">
    <section class="col-span-1 overflow-hidden">
        <a href="{{ $event->routeShow() }}">
            @if ($event->imagethumb and is_file($event->imagethumb))
                <img data-src="{{ url($event->imagethumb) }}" class="lazyload rounded w-full" data-sizes="auto"
                    alt="{{ $event->title }}">
            @elseif($event->imagecard and is_file($event->imagecard))
                <img data-src="{{ url($event->imagecard) }}" class="lazyload rounded w-full" data-sizes="auto"
                    alt="{{ $event->title }}">
            @else
                <img data-src="{{ asset('images/foto.jpg') }}" class="lazyload rounded w-full" data-sizes="auto"
                    alt="{{ $event->title }}">
            @endif
        </a>
    </section>

    <section class="col-span-5">
        <div class="post-header">
            <div class="title flex justify-between">
                <h5 class="text-lg font-semibold"><a href="{{ $event->routeShow() }}">
                        {{ $event->title }}
                        @if ($event->online_link)
                            - Online
                        @endif
                    </a>
                </h5>

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

    </section>

    <section class="col-span-2">
        {{-- <strong class="pull-right">{{ localized_date('l', $event->dateStart) }}</strong><br> --}}
        {{-- <span class="">{{ $event->organization->city }} </span> --}}
        <a href="?location={{ $event->village->district->id }}">
            <div class="">{{ $event->village->district->name }}</div>
        </a>
        <div class="">{{ $event->start_at->diffForHumans() }}</div>
        <a href="?organization={{ $event->organization_id }}">
            <div class="">Pridal: {{ $event->organization->title }}</div>
        </a>
    </section>
</section>

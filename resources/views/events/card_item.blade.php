<div class="font-semibold mb-3 w-fit border-b-4 border-gray-400">
    {{ ucfirst(localized_date('l', $event->start_at)) }}
    {{ localized_date('d. m. Y', $event->start_at) }}
</div>

<section class="mb-10 hover:bg-gray-50 p-2 border-2 rounded-md shadow-lg">
    <div class="md:grid grid-cols-8 gap-4 ">
        <section class="col-span-1 overflow-hidden">
            <a href="{{ $event->url['show'] }}">
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

        <div class="col-span-7">

            <div class="md:grid grid-cols-8 gap-4">
                <div class="col-span-6">
                    <div class="post-header">
                        <div class="title flex justify-between">
                            <h5 class="text-lg font-semibold"><a href="{{ $event->url['show'] }}">
                                    {{ $event->title }}
                                    @if ($event->online_link)
                                        - Online
                                    @endif
                                </a>
                            </h5>

                            @can('update', $event)
                                <dropdown-slot>
                                    @include('events._drop-down', ['item' => $event])
                                </dropdown-slot>
                            @endcan

                        </div>

                        <div class="text-gray-600 text-sm">
                            {{ html_entity_decode(strip_tags(\Illuminate\Support\Str::limit($event->body, 200))) }}
                        </div>
                    </div>
                </div>

                <div class="col-span-2 bg-gray-100 p-2 rounded-md">
                    {{-- <strong class="pull-right">{{ localized_date('l', $event->dateStart) }}</strong><br> --}}
                    {{-- <span class="">{{ $event->organization->city }} </span> --}}
                    <a href="?location={{ $event->village->district->id }}">
                        <div class="">{{ $event->village->district->name }}</div>
                    </a>
                    <div class="">{{ $event->start_at->diffForHumans() }}</div>

                    <div>Pridal:
                        <a href="?organization={{ $event->organization_id }}" class="hover:underline">
                            {{ $event->organization->title }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- ------- Admin panel ----------------- --}}
    @can('update', $event)
        <x-events.cards.footer-admin :event="$event" />
    @endcan
</section>

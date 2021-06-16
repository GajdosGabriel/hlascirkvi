<p class="font-semibold mb-3">
    {{ ucfirst(localized_date('l', $event->start_at)) }}
    {{ localized_date('d. m. Y', $event->start_at) }}
</p>
<div class="md:grid grid-cols-8 gap-4 mb-10">
    <div class="col-span-1">
        <a href="{{ $event->url }}">
            @if ($event->images()->whereType('img')->exists())
                @foreach ($event->images()->whereType('img')->get()
    as $image)
                    <img data-src="{{ url($image->ThumbImageUrl) }}" class="lazyload rounded w-full" data-sizes="auto"
                        alt="{{ $event->title }}">
                @break
            @endforeach
        @else
            @foreach ($event->images()->whereType('card')->get()
    as $image)
                <img data-src="{{ url($image->ThumbImageUrl) }}" class="lazyload rounded w-full" data-sizes="auto"
                    alt="{{ $event->title }}">
            @break
            @endforeach
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
            <div class="flex space-x-1">
                @if (!$event->displayStatus())
                    <div>{{ $event->count_view }} <label class="badge badge-default " title="Podujatie sa skončilo">
                            {{ trans('web.events_users_finished') }}</label>
                    </div>
                @elseif($event->published)
                    <div title="Kliknutím pozastavíte publikovanie akcie.">
                        <label class="badge badge-info"
                            title="Pozastaviť zobrazovanie?">{{ trans('web.events_users_is_active') }}</label>
                    </div>
                @else
                    <div title="Spustíť publikovanie akcie.">{{ $event->count_view }} <label class="badge badge-danger"
                            style="cursor: pointer">{{ trans('web.events_users_no_active') }}</label>
                    </div>
                @endif

                <div>
                    <i title="Počet zobrazení" class="fa fa-eye"> {{ $event->count_view }} </i>
                    <a href="{{ route('event.admin', [$event->id, $event->slug]) }}">Prihlásených:
                        {{ $event->eventSubscribe()->count() }}</a>
                </div>

            </div>
        @endcan

    </div>
    <div class="col-span-2 flex flex-col">
        {{-- <strong class="pull-right">{{ localized_date('l', $event->dateStart) }}</strong><br> --}}
        <span class="">{{ $event->organization->city }} </span>
        <span class="">{{ $event->start_at->diffForHumans() }}</span>
        <a href="?district={{ $event->village->district->id }}">
            <span class="">Miesto: {{ $event->village->district->name }}</span>
        </a>
        <span class="">Pridal: {{ $event->organization->title }}</span>
    </div>
</div>

<p class="font-semibold mb-3">
    {{ ucfirst( localized_date('l', $event->start_at) ) }}
    {{ localized_date('d. m. Y', $event->start_at) }}
</p>
<div class="md:grid grid-cols-8 gap-4 mb-10">
    <div class="col-span-1">
        <a href="{{ $event->url }}">
            @if ($event->images()->whereType('img')->exists())
                @foreach($event->images()->whereType('img')->get() as $image)
                    <img data-src="{{ url($image->ThumbImageUrl) }}" class="lazyload rounded w-full" data-sizes="auto"
                         alt="{{ $event->title }}">
                    @break
                @endforeach
            @else
                @foreach($event->images()->whereType('card')->get() as $image)
                    <img data-src="{{ url($image->ThumbImageUrl) }}" class="lazyload rounded w-full" data-sizes="auto"
                         alt="{{ $event->title }}">
                    @break
                @endforeach
            @endif
        </a>
    </div>

    <div class="col-span-5">
        <div class="post-header">
            <div class="title level">
                <h5 class="text-lg font-semibold"><a href="{{ $event->url }}">{{ $event->title }}</a></h5>

                @can('update', $event)
                    <article-admin inline-template>
                        <div v-cloak class="relative">
                            <i style="float: right; cursor: pointer " @click='toggle' title="Spravovať článok"
                               class="fas fa-ellipsis-v"></i>
                            <ul class="absolute right-0 border-2 border-gray-600 rounded-md bg-white" v-if="open">
                                <li class="px-2 py-1 hover:bg-gray-200 hover:font-semibold">
                                    <a href="{{ route('event.edit', [$event->id, $event->slug]) }}"
                                       class="">upraviť</a></li>
                                <li class="px-2 py-1 hover:bg-gray-200 ">
                                    <a href="{{ route('event.delete', [$event->id, $event->slug]) }}"
                                       class="hover:font-semibold">zmazať</a></li>
                                <li class="px-2 py-1 hover:bg-gray-200 hover:font-semibold">
                                    <a href="{{ route('event.admin', [$event->id, $event->slug]) }}"
                                       class="">Administrácia</a></li>
                            </ul>
                            {{--<a class="btn" href="{{ URL::previous() }}"> <i class="fa fa-arrow-left"></i> Späť</a>--}}
                        </div>
                    </article-admin>
                @endcan
            </div>

            <div class="text-gray-600">
                {{ html_entity_decode( strip_tags( \Illuminate\Support\Str::limit( $event->body, 200 ))) }}
            </div>
        </div>


        {{--------- Admin panel -------------------}}
        @can('update', $event)
            <div class="flex space-x-1">
                @if(! $event->displayStatus())
                    <div>{{ $event->count_view }} <label class="badge badge-default "
                                                         title="Podujatie sa skončilo"> {{ trans('web.events_users_finished') }}</label>
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
                    <a href="{{ route('event.admin', [$event->id, $event->slug]) }}">Prihlásených: {{ $event->eventSubscribe()->count() }}</a>
                </div>

            </div>
        @endcan

    </div>
    <div class="col-span-2 flex flex-col">
        {{--                <strong class="pull-right">{{ localized_date('l', $event->dateStart) }}</strong><br>--}}
        <span class="">{{ $event->organization->city }} </span>
        <span class="">{{ $event->start_at->diffForHumans() }}</span>
        <span class="">Miesto: {{ $event->village->district->name }}</span>
        <span class="">Pridal: {{ $event->organization->title }}</span>
    </div>
</div>

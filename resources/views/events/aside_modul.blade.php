@if($events->count())
    <section class="event mt-6">

        <div class="border-2 border-gray-300 rounded-sm">

            <div class="flex justify-between bg-blue-300 p-2 items-center mb-2">
                <span>{{ trans('web.events_modul_title') }}</span>
                <i class="fa fa-share-alt" aria-hidden="true"></i>
            </div>

            @forelse( $events as $event)
                <a href="{{ route('event.show', [$event->id, $event->slug]) }}">
                    <div class="card-body p-3 flex">

                            @if ($event->images()->whereType('img')->exists())
                                @foreach($event->images()->whereType('img')->get() as $image)
                                    <img data-src="{{ url($image->ThumbImageUrl) }}" class="lazyload w-24 mr-4 rounded-md" data-sizes="auto"
                                         title="Bez obrázka">
                                    @break
                                @endforeach
                            @else
                                @foreach($event->images()->whereType('card')->get() as $image)
                                    <img data-src="{{ url($image->OriginalImageUrl ) }}" class="lazyload w-24 mr-4 rounded-md"
                                         data-sizes="auto" title="Bez obrázka">
                                    @break
                                @endforeach
                            @endif
                            <div class="flex flex-col">
                                <h4 class="font-semibold" title="{{ $event->title }}">
                                    {{ Str::limit($event->title , 45, $end=' ...') }}
                                </h4>
                                <div>{{ $event->village->district->name }}</div>
                                {{--                            <div>začiatok {{ $event->dateStart->diffForHumans() }}</div>--}}
                            </div>
                        </div>

                </a>
            @empty
                <div class="card-body">{{ trans('web.events_empty') }}
                    @if(auth()->guest())
                        <a href="{{ url('login') }}">{{ trans('web.events_new_add') }}</a>
                    @endif
                </div>
            @endforelse

            @if($events->count() > 0)
                <div class="flex justify-between mx-2 mb-2">

                    <a class="hover:bg-gray-300 p-1 px-2 rounded-md" href="{{ route('event.create') }}">
                        <strong><i class="fas fa-plus"></i> Pridať akciu</strong>
                    </a>

                    <a class="hover:bg-gray-300 p-1 px-2 rounded-md" href="{{ route('event.index') }}">
                        <strong>{{ trans('web.events_next') }} <i class="fa fa-arrow-right" aria-hidden="true"></i></strong>
                    </a>
                </div>
            @endif

        </div>

    </section>

@endif


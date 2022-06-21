@if ($event->registration != 'no')
    <ticket-form inline-template>
        @include('events._form_ticket')
    </ticket-form>
@endif


<div class="">
    {{-- vizitka --}}
    @if ($event->images()->whereType('card')->exists())
        <img alt="" data-src="{{ url($event->images()->whereType('card')->first()->original_image_url) }}"
            class="lazyload rounded mb-6" data-sizes="auto">
    @endif

    {{-- Prihlasovanie pre prihláseného usera --}}
    <div class="hover:text-gray-900 pl-2 rounded-sm">
        @if (auth()->check() and !$event->isSubscribed())
            <form method="post" action="{{ route('event.favorite.store', [$event->id]) }}" class=" mb-4">
                @csrf @method('POST')
                @if ($event->isFavorited())
                    <button type="submit" class="btn btn-succenss w-full">
                        <i title="Zrušiť prihlásenie" class="fas fa-check"></i>
                        <span class="font-semibold">Odoslaná žiadosť o
                            nahrávku!</span>
                    </button>
                @else
                    <button type="submit" class="btn btn-default w-full">
                        Nemôžem prísť, chcem nahrávku.
                    </button>
                @endif
            </form>
        @else
            {{--  --}}
        @endif

        <form method="post" action="{{ route('event.subscribe.store', [$event->id]) }}">
            @csrf @method('POST')
            @if ($event->isSubscribed())
                <button type="submit" class="btn btn-success w-full">
                    <i class="fas fa-check"></i>
                    Ste prihlásený na akciu
                </button>
            @else
                <button type="submit" class="btn btn-primary w-full">
                    Prihlásiť sa na akciu
                </button>
            @endif
        </form>
    </div>

    {{-- Modul event panel info --}}
    @can('update', $event)
        @if (!$event->registration != 'no')
            <div class="flex justify-between border-gray-300 shadow-md border-2 p-2 rounded-md mt-6">
                <div class="text-center ">
                    <div>{{ $event->activeSubscribed() + $event->ticket_staff }}</div>
                    <div>prihlásených</div>
                </div>
                <div class="text-center ">
                    @if ($event->ticket_available == 0)
                        <span>&#8734;</span>
                    @else
                        <div>{{ $event->ticket_available }}</div>
                    @endif
                    <div>voľných miest</div>
                </div>
                <div class="text-center ">
                    <div>{{ now()->diffInDays($event->start_at->format('d-m-Y')) }}</div>
                    <div>dni do začiatku</div>
                </div>
            </div>

            <event-info-panel inline-template v-cloak>

                <div>
                    <div class="text-right cursor-pointer " @click="toggle">Upraviť</div>
                    <form v-if="show" method="post"
                        action="{{ route('event.eventInfoPanel', [$event->id, $event->slug]) }}" class="">
                        @csrf {{ method_field('put') }}

                        <div class="form-group">
                            <label for="">Počet miest, kapacita miestnosti</label>
                            <input type="number" name="ticket_available"
                                value="{{ old('ticket_available') ?? $event->ticket_available }}"
                                placeholder="Kapacita miestností" class="form-control input-sm">
                        </div>

                        <div class="form-group">
                            <label for="">Počet už obsadených miest, organizátori a pod.</label>
                            <input type="number" name="ticket_staff"
                                value="{{ old('ticket_staff') ?? $event->ticket_staff }}"
                                placeholder="Počet už obsadených miest" class="form-control input-sm">
                        </div>

                        <div class="form-group">
                            <button class="btn btn-small">Uložiť</button>
                        </div>

                    </form>
                </div>

            </event-info-panel>
        @endif
    @endcan

    {{-- aside info panel only if need it --}}
    @if ($event->registration == 'yes' or $event->registration == 'recomended')
        <div class="border-gray-300 shadow-md border-2 rounded-md mt-6">
            <div class="card-header bg-gray-200 px-2">{{ trans('web.events_info_panel') }}</div>
            <div class="p-2">

                <div class="flex justify-between">
                    <span>Pridal:</span>
                    <span>{{ $event->organization->title }}</span>
                </div>

                {{-- <div class="level"> --}}
                {{-- <span>Pridal:</span> --}}
                {{-- <span><a href="#"> {{ $event->organization->title }}</a></span> --}}
                {{-- </div> --}}

                <div class="flex justify-between">
                    <span>Registrácia:</span>
                    @if ($event->registration == 'yes')
                        <span>Áno</span>
                    @endif

                    @if ($event->registration == 'no')
                        <span>Nie</span>
                    @endif

                    @if ($event->registration == 'recomended')
                        <span>Doporučená</span>
                    @endif
                </div>


                <div class="flex justify-between">
                    <span>Vstupné:</span>
                    @if ($event->entryFee == 'voluntarily')
                        <span>Dobrovoľné</span>
                    @endif
                    @if ($event->entryFee == 'no')
                        <span>Nie</span>
                    @endif
                    @if ($event->entryFee == 'yes')
                        <span>Áno</span>
                    @endif
                </div>

                @if (isset($event->user->phone))
                    <div class="flex justify-between">
                        <span>Tel.:</span>
                        <span>{{ $event->user->phone }}</span>
                    </div>
                @endif
            </div>
        </div>

    @endif


    {{-- <div style="margin-top: 3rem">
        <event-comments-look :event="{{ $event }}" :commentsoffer="{{ $commentsLook }}">
        </event-comments-look>
        <event-comments-offer :event="{{ $event }}" :commentsoffer="{{ $commentsOffer }}">
        </event-comments-offer>
    </div> --}}


</div>

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


        @if ($event->isSubscribed())
            <form method="post" action="{{ route('event.subscribe.store', [$event->id]) }}">
                @csrf @method('POST')
                <button type="submit" class="btn btn-success w-full">
                    <i class="fas fa-check"></i>
                    Ste prihlásený na akciu
                </button>
            </form>
        @else
            @guest
                <form method="post" action="{{ route('event.subscribeGuest.store', [$event->id]) }}">
                    @csrf @method('POST')
                    <dropdown-slot>
                        <template v-slot:button>
                            <button class="btn btn-primary w-full">
                                Prihlásiť sa na akciu
                            </button>
                        </template>

                        <input type="text" name="first_name" class="border-2 border-gray-500 rounded-md p-2 mb-3"
                            value="{{ old('first_name') }}" required placeholder="Meno">

                        <input type="text" name="last_name" class="border-2 border-gray-500 rounded-md p-2"
                            value="{{ old('last_name') }}" required placeholder="Priezvisko">

                        <input type="email" name="email" class="border-2 border-gray-500 rounded-md p-2 mb-3"
                            value="{{ old('email') }}" required placeholder="email na zaslanie linku">

                        <button type="submit" class="btn btn-primary w-full">
                            Prihlásiť sa na akciu
                        </button>
                    </dropdown-slot>
                </form>
            @else
                <form method="post" action="{{ route('event.subscribe.store', [$event->id]) }}">
                    @csrf @method('POST')
                    <button type="submit" class="btn btn-primary w-full">
                        Prihlásiť sa na akciu
                    </button>
                </form>
            @endguest
        @endif

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
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M112 128c0-29.5 16.2-55 40-68.9V256h48V48h48v208h48V59.1c23.8 13.9 40 39.4 40 68.9v128h48V128C384 57.3 326.7 0 256 0h-64C121.3 0 64 57.3 64 128v128h48zm334.3 213.9l-10.7-32c-4.4-13.1-16.6-21.9-30.4-21.9H42.7c-13.8 0-26 8.8-30.4 21.9l-10.7 32C-5.2 362.6 10.2 384 32 384v112c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V384h256v112c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V384c21.8 0 37.2-21.4 30.3-42.1z"/></svg>
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

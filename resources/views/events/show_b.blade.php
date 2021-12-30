@if ($event->registration != 'no')
    <ticket-form inline-template>
        @include('events._form_ticket')
    </ticket-form>
@endif


<div class="">

    @if($event->Imagecard)
        <img alt=""
            data-src="{{ url(
                $event->images()->whereType('card')->first()->original_image_url,
            ) }}"
            class="lazyload rounded mb-6" data-sizes="auto">


        {{-- vizitka --}}
    @endif
    <div class="cursor-pointer pl-2 rounded-sm">
        <a href="{{ route('event.record', [$event->id]) }}">
            @if ($event->isFavorited())
                <i class="fas fa-check"></i> <span class="font-semibold"> Odoslaná žiadosť o
                    nahrávku!</span>
            @else
                <i class="fas fa-volume-up"></i> Nemôžem prísť, chcem nahrávku.
            @endif
        </a>
    </div>

    {{-- Prihlasovanie pre prihláseného usera --}}
    <div class="cursor-pointer hover:text-gray-900 pl-2 rounded-sm">
        @if (auth()->check())
            <form method="post" action="{{ route('favorites.update', [$event->id]) }}">
                @csrf @method('PUT')
                <input name="model" value="Event" type="hidden">
                <input name="model_id" value="{{ $event->id }}" type="hidden">
                @if ($event->isSubscribed())
                    <button type="submit">
                        <i title="Zrušiť prihlásenie" class="fas fa-check"></i>
                        <span class="font-semibold"> Ste prihlásený! </span>
                    </button>
                @else
                    <button type="submit">
                        <i class="fas fa-check"></i>
                        Prihlásiť sa ako {{ auth()->user()->fullName }}
                    </button>
                @endif
            </form>
        @else
            <form method="post" action="{{ route('favorites.update', [$event->id]) }}">
                @csrf @method('PUT')
                <i class="fas fa-check"></i>
                <input name="model" value="Event" type="hidden">
                <input name="model_id" value="{{ $event->id }}" type="hidden">
                <button type="submit">
                    Chcem sa prihlásiť na akciu.
                </button>
            </form>
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
                    <div>voľných miest</div>
                </div>
                <div class="text-center ">
                    <div>{{ now()->diffInDays($event->start_at->format('d-m-Y')) }}</div>
                    <div>dni do začiatku</div>
                </div>
            </div>

            <event-info-panel inline-template v-cloak>

                <div>
                    <div @click="toggle">Upraviť</div>
                    <form v-if="show" method="post"
                        action="{{ route('event.eventInfoPanel', [$event->id, $event->slug]) }}" class="">
                        @csrf {{ method_field('put') }}

                        <div class="
                                        form-group">
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
        <span>Doporučená</span> )
        <div class="
                    card">
            <div class="card-header">{{ trans('web.events_info_panel') }}</div>
            <div class="card-body">

                <div class="flex">
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

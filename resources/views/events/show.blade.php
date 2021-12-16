@extends('layouts.app')

@section('title') <title>{{ $event->title }}</title> @endsection


@section('othermeta')
    <title>{{ $event->title }}</title>
    <meta property="fb:app_id" content="241173683337522" />
    <meta property="og:url" content="{{ $event->url }}" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="{{ $event->title }}" />
    <meta property="og:description" content="{!! \Illuminate\Support\Str::limit($event->body, 130) !!}" />
    <meta property="og:image" content="@forelse($event->images as $image)  @if ($loop->first) {{ url($image->originalImageUrl) }} @endif @empty @endforelse" />
    <meta property="og:image:width" content="600" />
    <meta property="og:image:height" content="500" />
    <meta property="og:image:alt" content="{{ $event->title }}" />
@endsection

@section('content')




    <div class="page">

        <div class="md:flex   relative">

            <div class="md:w-8/12">


                @component('layouts.components.pages.page_title')
                    @slot('title')

                        {{ $event->title }}

                    @endslot

                    @slot('title_right')

                        @can('update', $event)
                            <event-dropdown :post="{{ $event }}" />
                        @endcan

                    @endslot
                @endcomponent

                <div class="border-2 rounded-md border-gray-500 p-4 mb-6 shadow-md">
                    <span>{{ ucfirst(localized_date('l', $event->start_at)) }}
                        {{ localized_date('H.i', $event->start_at) }} hod.</span>
                    <h5>{{ trans('web.events_city') }}
                        {{ $event->village->fullname }}, {{ $event->street }}
                        <br>
                        {{-- {{ $event->start_at->format('d. m. Y')}}, o {{ $event->start_at->format('H.i') }} hod. --}}
                        {{-- - {{ $event->end_at->format('d. m. Y')}}, o {{ $event->end_at->format('H.i') }} hod. --}}

                        @if ($event->start_at->diffInDays($event->end_at))
                            Od: {{ $event->start_at->format('d. m. Y') }}, {{ $event->start_at->format('H.i') }} hod.
                            do: {{ $event->end_at->format('d. m. Y') }}, {{ $event->end_at->format('H.i') }} hod.
                        @else
                            Dňa: {{ $event->start_at->format('d. m. Y') }}, o {{ $event->start_at->format('H.i') }} -
                            {{ $event->end_at->format('H.i') }} hod.
                        @endif
                    </h5>
                </div>

                <div class="">

                    {!! $event->body !!}

                    <p>Akciu zverejnil: {{ $event->organization->title }}</p>

                    @if ($event->images()->whereType('img')->exists())
                        @foreach ($event->images()->whereType('img')->get()
    as $image)
                            {{-- <img alt="{{ $image->title }}" data-src="{{ url($image->OriginalImageUrl) }}"  class="lazyload rounded"  data-sizes="auto"> --}}

                            <event-picture-viewer inline-template class="
                    my-6">
                    <div :class="{'fixed inset-0 bg-gray-600' : open}">
                        <div v-if="open" @click="showModal"
                            class="absolute right-0 border-gray-200 border-2 bg-gray-700 hover:bg-gray-500 rounded px-4 py-2 text-white font-semibold text-2xl cursor-pointer ">
                            Zrušiť x
                        </div>
                        <img alt="{{ $image->title }}" src="{{ url($image->OriginalImageUrl) }}" @click="showModal"
                            class="rounded cursor-pointer">

                    </div>
                    </event-picture-viewer>

                    @endforeach
                    @endif




                    {{-- // Facebook --}}

                    {{-- <div --}}
                    {{-- class="fb-like" --}}
                    {{-- data-share="true" --}}
                    {{-- data-width="450" --}}
                    {{-- data-show-faces="true"> --}}
                    {{-- </div> --}}

                    <div>
                        @foreach ($event->images as $image)
                            @if ($image->mime == 'pdf' or $image->mime == 'doc' or $image->mime == 'docx' or $image->mime == 'txt')
                                <a target="_blank" href="{{ route('events.download', [$image->url]) }}"
                                    alt="{{ $image->title }}">
                                    <i class="far fa-file-pdf fa-4x"></i>
                                    Príloha {{ $image->org_name }}
                                </a>
                            @endif
                        @endforeach
                    </div>

                    {{-- <div> --}}
                    {{-- <a style="float:right;text-decoration: none;background: #09c; color: whitesmoke" class="btn btn-primary">Vstupenka</a> --}}
                    {{-- </div> --}}

                </div>


                {{-- @if (isset($event->appendFile)) --}}
                {{-- <strong>Príloha: </strong> <a target="_blank" href="{{ Storage::url($event->appendFile) }}"><i class="fa fa-file-o" aria-hidden="true"></i> Príloha na stiahnutie</a> --}}
                {{-- @endif --}}


                {{-- <h4>{{ trans('web.events_time_to_start') }} {{ $event->dateStart->diffForHumans() }}.</h4> --}}

                {{-- @if (isset($event->clientwww)) --}}
                {{-- <p><a target="_blank" href="{{ $event->clientwww }}">{{ trans('web.btn_link_to_web') }}</a></p> --}}
                {{-- @endif --}}

                @if ($event->registration != 'no')
                    <ticket-form inline-template>
                        @include('events._form_ticket')
                    </ticket-form>
                @endif


            </div>


            <div class="md:w-4/12 md:p-6">

                <div class="">

                    @if ($event->Imagecard)
                    <img alt="
                    {{ $image->title }}"
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
                                        action="{{ route('event.eventInfoPanel', [$event->id, $event->slug]) }}"
                                        class="">
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

                <div class="">
                        {{-- <h4>Registračný formulár</h4> --}}
                        {{-- <form action="{{ route('event.subscriptions', $event->id) }}" method="post"> --}}
                        {{-- {{ csrf_field() }} --}}
                        {{-- @if ($event->registration == 'recomended' && $event->IsSubscribedTo) --}}
                        {{-- <p>{{ trans('web.events_rezervation_recomended') }}</p> --}}
                        {{-- <button type="submit" name="registration" value="0" class="btn">{{ trans('web.events_rezervation_OK') }}</button> --}}
                        {{-- <a href=""><small>{{ trans('web.btn_unregister') }}</small></a> --}}
                        {{-- @elseif($event->registration == 'recomended') --}}
                        {{-- <p>{{ trans('web.events_rezervation_recomended') }}</p> --}}

                        {{-- @include('events._reservation_button') --}}

                        {{-- @elseif($event->registration == 'yes' && $event->IsSubscribedTo) --}}
                        {{-- <p>{{ trans('web.events_rezervation_need') }}</p> --}}
                        {{-- <button type="submit" name="registration" value="0" class="btn">{{ trans('web.events_rezervation_OK') }}</button> --}}
                        {{-- <a href=""><small>{{ trans('web.btn_unregister') }}</small></a> --}}
                        {{-- @elseif($event->registration == 'yes') --}}
                        {{-- <p class="text-muted mb-2">{{ trans('web.events_rezervation_need') }}</p> --}}

                        {{-- @include('events._reservation_button') --}}

                        {{-- @endif --}}
                        {{-- </form> --}}


                    </div>

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


                <div style="margin-top: 3rem">
                    <event-comments-look :event="{{ $event }}" :commentsoffer="{{ $commentsLook }}">
                    </event-comments-look>
                    <event-comments-offer :event="{{ $event }}" :commentsoffer="{{ $commentsOffer }}">
                    </event-comments-offer>
                </div>


            </div>

        </div>


    </div>
    </div>

@endsection

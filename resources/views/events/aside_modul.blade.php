@if ($events->count())
    <section class="card">

        <header class="card_header">
            <span>{{ trans('web.events_modul_title') }}</span>
            <i class="fa fa-share-alt" aria-hidden="true"></i>
        </header>


        <div class="border-2 border-gray-300 rounded-sm ">

            @forelse( $events as $event)
                <a href="{{ $event->url }}">
                    <div class="card-body p-3 grid grid-cols-6 gap-3">
                        <div class="col-span-2">
                            @if ($event->Imagethumb)
                                <img data-src="{{ url($event->Imagethumb) }}" class="lazyload mr-4 rounded-md"
                                    data-sizes="auto" title="{{ $event->title }}">
                            @else
                                @foreach ($event->images()->whereType('card')->get()
    as $image)
                                    <img data-src="{{ url($image->OriginalImageUrl) }}"
                                        class="lazyload mr-4 rounded-md" data-sizes="auto" title="Bez obrázka">
                                @break
                            @endforeach
            @endif
        </div>
        <div class="col-span-4">
            <div class="flex flex-col">
                <h4 class="font-semibold" title="{{ $event->title }}">
                    {{ Str::limit($event->title, 45, $end = ' ...') }}
                </h4>
                <div>{{ $event->village->district->name }}</div>
                <div class="font-semibold">začiatok {{ $event->start_at->diffForHumans() }}</div>
            </div>
        </div>
        </div>
        </a>
        @empty
            <div class="card-body">{{ trans('web.events_empty') }}
                @if (auth()->guest())
                    <a href="{{ url('login') }}">{{ trans('web.events_new_add') }}</a>
                @endif
            </div>
    @endforelse

    @if ($events->count() > 0)
        <div class="flex justify-between mx-2 mb-2 text-sm">

            <div class="flex items-center">
                <svg class="w-5 h-5 fill-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>

                <a class="hover:bg-gray-300 p-1 px-2 rounded-md" href="{{ route('akcie.create') }}">
                    Pridať akciu
                </a>
            </div>
            <div class="flex items-center">
                <a class="hover:bg-gray-300 p-1 px-2 rounded-md" href="{{ route('akcie.index') }}">
                    {{ trans('web.events_next') }}
                </a>
                <svg class="w-5 h-5 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                    fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10.293 15.707a1 1 0 010-1.414L14.586 10l-4.293-4.293a1 1 0 111.414-1.414l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0z"
                        clip-rule="evenodd" />
                    <path fill-rule="evenodd"
                        d="M4.293 15.707a1 1 0 010-1.414L8.586 10 4.293 5.707a1 1 0 011.414-1.414l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0z"
                        clip-rule="evenodd" />
                </svg>

            </div>
        </div>
    @endif

    </div>

    </section>

    @endif

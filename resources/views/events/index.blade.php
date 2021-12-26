@extends('layouts.app')
@section('title') <title>{{ 'Kresťanské akcie' }}</title> @endsection

@section('content')

    @component('components.pages.page_3_2')
        @slot('page_full')
            @include('events._current_events')
        @endslot

        @slot('page')

            @component('components.pages.page_title')
                @slot('title')
                    {{ $title ?? 'Pozvánky na podujatia' }}
                @endslot

                @slot('title_right')
                    @auth
                        <a class="border-2 border-blue-400 p-1 px-2 rounded-md shadow-sm hover:bg-blue-300"
                            href="{{ route('organization.event.create', auth()->user()->org_id) }}"><i class="fas fa-plus"></i>
                            Nové podujatie
                        </a>
                    @else
                        <a class="border-2 border-blue-400 p-1 px-2 rounded-md shadow-sm hover:bg-blue-300" href="{{ route('login') }}"><i
                                class="fas fa-plus"></i>
                            Nové podujatie
                        </a>
                    @endauth
                @endslot
            @endcomponent

            {{-- Upcoming events --}}
            @forelse($events as $event)
                @include('events._list_items')
            @empty
                bez podujatí
            @endforelse

            {{ $events->links() }}

        @endslot

        {{-- Aside section --}}
        @slot('aside')

            @include('events.districts_modul')
            @include('events.finished_event_modul')

        @endslot
    @endcomponent

@endsection

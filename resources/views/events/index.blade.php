@extends('layouts.app')
@section('title') <title>{{ 'Kresťanské akcie' }}</title> @endsection
@section('content')

    <div class="container mx-auto p-5">
        @include('events._current_events')
        <div class="md:flex">

        <div class="md:w-8/12 md:p-4 ">


            <div class="flex flex justify-between">
                <h2 class="font-semibold text-2xl">  {{ $title ?? "Pozvánky na podujatia" }}</h2>

                <a class="border-2 border-blue-400 p-1 px-2 rounded-md shadow-sm hover:bg-blue-300" href="{{ route('event.create') }}"><i
                        class="fas fa-plus"></i>
                    Nové podujatie
                </a>
            </div>

            {{--  Upcoming events --}}
            @forelse($events as $event)
                @include('events._list_items')
            @empty
                bez podujatí
            @endforelse

            {{ $events->links() }}
        </div>

        <div class="md:w-4/12 p-4">
            {{--@include('events.regions_modul')--}}
            @include('events.districts_modul')
            @include('events.finished_event_modul')
        </div>
    </div>
    </div>



@endsection

@extends('layouts.app')
@section('title') <title>{{ 'Kresťanské akcie' }}</title> @endsection
@section('content')

    <div class="container">

        <div class="page">

            <div class="page-content">

                <div class="page-title">

                    <h2>  {{ $title ?? "Pozvánky na podujatia" }}</h2>

                    <a class="btn btn-small" href="{{ route('event.create') }}"><i class="fas fa-plus"></i> Nové
                        podujatie</a>
                </div>

                @forelse($events as $event)
                    @include('events._list_items')
                @empty
                    bez podujatí
                @endforelse

                {{ $events->links() }}
            </div>

            <div class="page-aside">
                {{--@include('events.regions_modul')--}}
                @include('events.districts_modul')
                @include('events.finished_event_modul')
            </div>
            ,


        </div>

    </div>


@endsection

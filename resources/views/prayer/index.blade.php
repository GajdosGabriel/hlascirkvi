@extends('layouts.app')
@section('title') <title>{{ 'Modlitebný múr' }}</title> @endsection
@section('content')

    <div class="container">

        <div class="page">

            <div class="page-content">

                <div class="page-title">
                    <h2>  {{ $title ?? "Modlitebný múr" }}</h2>


                    <new-prayer-index-page/>

                </div>


                <prayer-card></prayer-card>

                {{--  Upcoming events --}}
                {{-- @forelse($events as $event)
                    @include('events._list_items')
                @empty
                    bez podujatí
                @endforelse --}}

                {{-- {{ $events->links() }} --}}
            </div>

            <div class="page-aside">
                {{--@include('events.regions_modul')--}}
                {{-- @include('events.districts_modul') --}}
                {{-- @include('events.finished_event_modul') --}}
            </div>
            ,


        </div>

    </div>




@endsection

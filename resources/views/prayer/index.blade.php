@extends('layouts.app')
@section('title') <title>{{ 'Modlitebný múr' }}</title> @endsection
@section('content')

    <div class="container mx-auto p-4 min-h-screen">

        <div class="md:w-6/12">

                <prayers-index-page></prayers-index-page>


            <div class="page-aside">
                {{--@include('events.regions_modul')--}}
                {{-- @include('events.districts_modul') --}}
                {{-- @include('events.finished_event_modul') --}}
            </div>
        </div>
    </div>




@endsection

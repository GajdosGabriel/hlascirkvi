@extends('layouts.app')
@section('title') <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title> @endsection

@section('content')

    @component('components.pages.profil')

        @slot('title')
            {{ $title ?? 'Pozvánky na podujatia' }}
        @endslot


        @slot('title_right')
            <a href="{{ route('organization.event.create', $organization->id) }}" class="btn btn-primary">Nová akcia</a>
        @endslot

        @slot('page')
        <div class="flex">

     
            <div class="md:w-2/3 mr-5">
                @include('events.show_a')
            </div>

            <div class="md:w-1/3">
                @include('events.show_b')
            </div>
        </div>
        @endslot
    @endcomponent

@endsection

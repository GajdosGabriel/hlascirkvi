@extends('layouts.app')

@section('content')

    @component('components.pages.profil')


        @slot('title')

            Semináre panel

        @endslot

        @slot('title_right')

            <a href="{{ route('organization.seminar.create', $organization->id) }}" class="btn btn-default">Nový semimár</a>

        @endslot

        @slot('page')
            @include('profiles.seminars._list')

        @endslot
    @endcomponent


@endsection

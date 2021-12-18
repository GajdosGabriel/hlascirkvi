@extends('layouts.app')

@section('content')

    @component('layouts.components.pages.profil')
        @slot('page')


            @component('layouts.components.pages.page_title')
                @slot('title')

                    Semináre panel

                @endslot

                @slot('title_right')

                    <a href="{{ route('organization.seminar.create', $organization->id) }}" class="btn btn-default">Nový semimár</a>

                @endslot
            @endcomponent


            @include('profiles.seminars._list')


        @endslot
    @endcomponent


@endsection

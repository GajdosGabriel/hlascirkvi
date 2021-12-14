@extends('layouts.app')

@section('content')

    @component('layouts.pages.profil')
        @slot('page')
            <div class="col-span-8">

                @component('layouts.pages.page_title')
                    @slot('title')

                        Semináre panel

                    @endslot

                    @slot('title_right')

                        <a href="{{ route('organization.seminar.create', $organization->id) }}" class="btn btn-default">Nový semimár</a>

                    @endslot
                @endcomponent


                @include('profiles.seminars._list')

            </div>
        @endslot
    @endcomponent


@endsection

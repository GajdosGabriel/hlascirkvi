@extends('layouts.app')

@section('content')

    @component('layouts.pages.profil')
        @slot('page')
            <div class="col-span-5">

                @component('layouts.pages.page_title')
                    @slot('title')

                        Semináre panel

                    @endslot

                    @slot('title_site')

                        <div>
                            <a href="{{ route('seminars.create') }}" class="btn btn-default">Nový semimár</a>
                        </div>

                    @endslot
                @endcomponent


                @include('profiles.seminars._list')

            </div>
        @endslot
    @endcomponent


@endsection

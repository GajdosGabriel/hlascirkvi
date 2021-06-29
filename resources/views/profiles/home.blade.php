@extends('layouts.app')

@section('content')


    @component('layouts.pages.profil')
        @slot('page')

        <div class="col-span-8">

            @component('layouts.pages.page_title')
                @slot('title')

                    Profil home

                @endslot

            @endcomponent
        </div>

        @endslot
    @endcomponent


@endsection

@extends('layouts.app')

@section('content')


    @component('layouts.components.pages.profil')
        @slot('page')

            @component('layouts.components.pages.page_title')
                @slot('title')

                    Profil home

                @endslot

            @endcomponent


        @endslot
    @endcomponent


@endsection

@extends('layouts.app')

@section('content')


    @component('layouts.components.pages.profil')
        @slot('page')

        <div class="col-span-8">

            @component('layouts.components.pages.page_title')
                @slot('title')

                    Profil home

                @endslot

            @endcomponent
        </div>

        @endslot
    @endcomponent


@endsection

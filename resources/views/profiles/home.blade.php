@extends('layouts.app')

@section('content')


    @component('layouts.pages.profil')
        @slot('page')
            Profil home
        @endslot
    @endcomponent


@endsection

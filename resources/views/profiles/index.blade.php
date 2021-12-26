@extends('layouts.app')

@section('content')

    @component('components.pages.profil')

        @slot('title')
            Profil home
        @endslot

        @slot('page')
        {{--  --}}
        @endslot

    @endcomponent

@endsection

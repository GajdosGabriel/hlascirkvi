@extends('layouts.app')
@section('title') <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title> @endsection

@section('content')

    @component('components.pages.profil')

        @slot('title')
            Upraviť modlitbu
        @endslot

        @slot('title_right')
            <a class="btn btn-default" href="{{ route('user.prayer.index', auth()->user()->id) }}">
                Späť
            </a>
        @endslot


        @slot('page')
            <form action="{{ route('user.prayer.update', [$user->id, $prayer->id]) }}" method="post" class="md:w-1/2">
                @csrf @method('PUT')
                @include('prayers._form')
            </form>
        @endslot
    @endcomponent

@endsection

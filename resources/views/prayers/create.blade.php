@extends('layouts.app')
@section('title') <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title> @endsection

@section('content')

    <x-pages.admin>

        <x-slot name="title">
            Nová modlitba
        </x-slot>

        <x-slot name="title_right">
            <a class="btn btn-default" href="{{ route('user.prayer.index', auth()->user()->id) }}">
                Späť
            </a>
        </x-slot>


        <x-slot name="page">
            <form action="{{ route('user.prayer.store', auth()->user()->id) }}" method="post" class="md:w-1/2">
                @csrf @method('POST')
                @include('prayers._form')
            </form>


        </x-slot>
    </x-pages.admin>

@endsection

@extends('layouts.dashboard')
@section('title')
    <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title>
@endsection

@section('content')
    <x-pages.dashboard>

        <x-slot name="title">
            Nová modlitba
        </x-slot>

        <x-slot name="title_right">
            <a class="btn btn-default" href="{{ route('profile.canals.prayers.index', auth()->user()->org_id) }}">
                Späť
            </a>
        </x-slot>


        <x-slot name="page">
            <form action="{{ route('profile.canals.prayers.store', auth()->user()->org_id) }}" method="post" class="md:w-1/2">
                @csrf @method('POST')
                @include('prayers._form')
            </form>


        </x-slot>
        </x-pages.dashboard>
    @endsection

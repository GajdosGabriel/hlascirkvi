@extends('layouts.app')

@section('title')
    <title>{{ "Všetky semináre {$organization->title}" }}</title>
@endsection

@section('content')
    <x-pages.dashboard>

        <x-slot name="title">

            Semináre panel

        </x-slot>

        <x-slot name="title_right">

            <a href="{{ route('profile.organization.seminar.create', $organization->id) }}" class="btn btn-default">
                Nový semimár
            </a>

        </x-slot>

        <x-slot name="page">

            @include('profile.seminars._list')

        </x-slot>
        </x-pages.admin>
    @endsection

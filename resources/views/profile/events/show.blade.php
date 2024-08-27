@extends('layouts.app')
@section('title')
    <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title>
@endsection

@section('content')
    <x-pages.dashboard-and-aside>

        <x-slot name="title">
            {{ $title ?? 'Pozvánky na podujatia' }}
        </x-slot>


        <x-slot name="title_right">
            <a href="{{ route('profile.event.create', $organization->id) }}" class="btn btn-primary">Nové podujatie</a>
        </x-slot>

        <x-slot name="page">
            <div class="flex">

                <div class="md:w-2/3 mr-5">
                    @include('events.show_a')
                </div>

                <div class="md:w-1/3">
                    @include('events.show_b')
                </div>
            </div>
        </x-slot>
    </x-pages.dashboard-and-aside>
@endsection

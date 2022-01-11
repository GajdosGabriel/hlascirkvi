@extends('layouts.app')
@section('title') <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title> @endsection

@section('content')

    <x-pages.admin>

        <x-slot name="title">
            Nový seminár
        </x-slot>

        <x-slot name="title_right">

        </x-slot>


        <x-slot name="page">
            <form class="" method="post"
                action="{{ route('organization.seminar.store', $organization->id) }}">
                @csrf @method('POST')

                @include('seminars.form')

            </form>
        </x-slot>
    </x-pages.admin>

@endsection

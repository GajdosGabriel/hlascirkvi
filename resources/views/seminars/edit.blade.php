@extends('layouts.app')
@section('title')
    <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title>
@endsection

@section('content')
    <x-pages.dashboard>

        <x-slot name="title">
            Upraviť seminár
        </x-slot>

        <x-slot name="title_right">

        </x-slot>


        <x-slot name="page">
            <form class="" method="post"
                action="{{ route('organization.seminar.update', [$organization->id, $seminar->id]) }}">
                @csrf @method('PUT')

                @include('seminars.form')
            </form>

        </x-slot>
        </x-pages.admin>
    @endsection

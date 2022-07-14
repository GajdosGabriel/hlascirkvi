@extends('layouts.app')
@section('title') <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title> @endsection

@section('content')

    <x-pages.admin>

        <x-slot name="title">
            Upraviť modlitbu
        </x-slot>

        <x-slot name="title_right">
            <a class="btn btn-default" href="{{ route('organization.prayer.index', $organization->id) }}">
                Späť
            </a>
        </x-slot>


        <x-slot name="page">
            <form action="{{ route('organization.prayer.update', [$organization->id, $prayer->id]) }}" method="post"
                class="md:w-1/2">
                @csrf @method('PUT')
                @include('prayers._form')
            </form>
        </x-slot>
    </x-pages.admin>

@endsection

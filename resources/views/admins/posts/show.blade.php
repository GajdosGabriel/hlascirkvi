@extends('layouts.app')
@section('title')
    <title>{{ 'Všetky články' }}</title>
@endsection

@section('content')
    <x-pages.dashboard>

        <x-slot name="title">
            Články
        </x-slot>

        <x-slot name="title_right">
            <a href="{{ route('profile.post.create') }}" class="btn btn-primary">Nový článok</a>
        </x-slot>


        <x-slot name="page">

            @include('posts.show')

        </x-slot>

        </x-pages.admin>
    @endsection

@extends('layouts.app')
@section('title')
    <title>{{ 'Nový článok' }}</title>
@endsection

@section('content')
    <x-pages.dashboard>


        @include('layouts.errors')


        <x-slot name="title">
            Vytvoriť článok
        </x-slot>

        <x-slot name="title_right">
            <a href="{{ url()->previous() }}" class="btn"> <i class="fa fa-arrow-left"></i> Späť</a>
        </x-slot>


        <x-slot name="page">
            <form method="post" action="{{ route('profile.organization.post.store', $organization->id) }}"
                enctype="multipart/form-data">
                @csrf @method('POST')
                @include('posts.form')
            </form>

            @include('posts.editor')
        </x-slot>
        </x-pages.admin>
    @endsection

@extends('layouts.app')
@section('title')
    <title>{{ 'Upraviť článok' }}</title>
@endsection

@section('content')
    <x-pages.dashboard-and-right>

        @include('layouts.errors')


        <x-slot name="title">

            Upraviť článok

        </x-slot>

        <x-slot name="title_right">

        </x-slot>

        <x-slot name="page">

            <form method="POST" action="{{ route('profile.post.update', $post->id) }}"
                enctype="multipart/form-data">
                @csrf @method('PUT')
                @include('posts.form')
            </form>

            @include('posts.editor')

        </x-slot>
        </x-pages.dashboard-and-right >
    @endsection

@extends('layouts.app')

@section('content')
    <x-pages.admin>

        @include('layouts.errors')


        <x-slot name="title">

            Upraviť článok

        </x-slot>

        <x-slot name="title_right">

        </x-slot>

        <x-slot name="page">

            <form method="POST" action="{{ route('organization.post.update', [$post->organization_id, $post->id]) }}"
                enctype="multipart/form-data">
                @csrf @method('PUT')
                @include('posts.form')
            </form>

            @include('posts.editor')

        </x-slot>
    </x-pages.admin>

@endsection

@extends('layouts.app')

@section('content')
    @component('components.pages.profil')

        @include('layouts.errors')


        @slot('title')

            Upraviť článok

        @endslot

        @slot('title_right')

        @endslot

        @slot('page')

            <form method="POST" action="{{ route('organization.post.update', [$post->organization_id, $post->id]) }}"
                enctype="multipart/form-data">
                @csrf @method('PUT')
                @include('posts.form')
            </form>

            @include('posts.editor')

        @endslot
    @endcomponent

@endsection

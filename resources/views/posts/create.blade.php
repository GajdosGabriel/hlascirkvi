@extends('layouts.app')

@section('content')

    @component('components.pages.profil')


        @include('layouts.errors')


        @slot('title')
            Vytvoriť článok
        @endslot

        @slot('title_right')
            <a href="{{ url()->previous() }}" class="btn"> <i class="fa fa-arrow-left"></i> Späť</a>
        @endslot


        @slot('page')
            <form method="post" action="{{ route('organization.post.store', $organization->id) }}" enctype="multipart/form-data">
                @csrf @method('POST')
                @include('posts.form')
            </form>

            @include('posts.editor')
        @endslot
    @endcomponent

@endsection

@extends('layouts.app')

@section('content')
    <div class="page">
        <div class="md:w-8/12">
            @include('layouts.errors')

            @component('layouts.pages.page_title')
                @slot('title')

                    Upraviť článok

                @endslot

                @slot('title_site')

                @endslot
            @endcomponent


            <form method="POST" action="{{ route('posts.update', [$post->id]) }}" enctype="multipart/form-data">
                @csrf @method('PUT')
                @include('posts.form')
            </form>
        </div>

        <div class="md:w-4/12">
            aside
        </div>
    </div>

    @include('posts.editor')

@endsection

@extends('layouts.app')

@section('content')
    @include('organizations._profil-menu')
    <div class="page">
        <div class="md:w-8/12">

            <div class="page_title">
                @include('layouts.errors')
               <h2 class="text-2xl">Upraviť článok</h2>
            </div>

            <form method="post" action="{{ route('post.update', [$post->id]) }}" enctype="multipart/form-data">
                @csrf
                @include('posts.form')
            </form>
        </div>

        <div class="md:w-4/12">
            aside
        </div>
    </div>

    @include('posts.editor')

    @endsection



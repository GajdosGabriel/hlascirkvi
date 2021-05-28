@extends('layouts.app')

@section('content')
    <div class="page">
        <div class="md:w-8/12">

            <div class="page_title">
                @include('layouts.errors')
               <h2 class="text-2xl">Upraviť článok</h2>
            </div>

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



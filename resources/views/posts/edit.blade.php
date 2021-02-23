@extends('layouts.app')

@section('content')

    <div class="page">

        <div class="w-8/12">

            <div class="page_title">
                @include('layouts.errors')
               <h2 class="text-2xl">Upraviť článok</h2>
            </div>

            <form method="post" action="{{ route('post.update', [$post->id]) }}" enctype="multipart/form-data">
                @csrf
                @include('posts.form')
            </form>

            <div class="w-4/12">
                aside
            </div>


        </div>

    </div>

    @include('posts.editor')

    @endsection



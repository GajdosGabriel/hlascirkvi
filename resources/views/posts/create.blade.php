@extends('layouts.app')

@section('content')

    <div class="page">
        @include('organizations._profil-menu')
        <div class="">

            <div class="p-2">
                @include('layouts.errors')
               <h2 class="page_title">Vytvoriť článok</h2>
                <form class="md:w-8/12" method="post" action="{{ route('post.store') }}" enctype="multipart/form-data">
                    @csrf
                    @include('posts.form')
                </form>
            </div>

            <div class="page-aside">
{{--                aside--}}
            </div>


        </div>

    </div>


    @endsection

@include('posts.editor')

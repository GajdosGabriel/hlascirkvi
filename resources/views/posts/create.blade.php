@extends('layouts.app')

@section('content')

    <div class="container">

        <div class="page">

            <div class="page-content">
                @include('layouts.errors')
               <h2>Vytvoriť článok</h2>
                <form method="post" action="{{ route('post.store') }}" enctype="multipart/form-data">
                    @csrf
                    @include('posts.form')
                </form>
            </div>

            <div class="page-aside">
                aside
            </div>


        </div>

    </div>


    @endsection

@include('posts.editor')
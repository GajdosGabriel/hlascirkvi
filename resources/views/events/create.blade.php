@extends('layouts.app')


{{--@section('title', $title)--}}


@section('content')

    <div class="page">
        @include('organizations._profil-menu')
        <form method="post" action="{{ route('event.store') }}" class="flex" enctype="multipart/form-data">  @csrf

            <div class="page-content">
                <div class="page_title">
                    <h2>Nové podujatie <a href="{{url()->previous()}}"></a></h2>
                    <a href="{{ url()->previous() }}" class="btn"> <i class="fa fa-arrow-left"></i> Späť</a>
                </div>
                    @include('events._form_a')
            </div>

                <div class="page-aside mx-5">
                    @include('events._form_b')

                </div>

        </form>
    </div>
@endsection

@include('posts.editor')

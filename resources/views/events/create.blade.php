@extends('layouts.app')


{{--@section('title', $title)--}}


@section('content')

    <div class="container">
        <form method="post" action="{{ route('event.store') }}" class="page" enctype="multipart/form-data">  @csrf

            <div class="page-content">
                <div class="page-title">
                    <h2>Nové podujatie <a href="{{url()->previous()}}"></a></h2>
                    <a href="{{ url()->previous() }}" class="btn"> <i class="fa fa-arrow-left"></i> Späť</a>
                </div>
                    @include('events._form_a')
            </div>

                <div class="page-aside">
                    @include('events._form_b')

                </div>

        </form>
    </div>
@endsection

@include('posts.editor')

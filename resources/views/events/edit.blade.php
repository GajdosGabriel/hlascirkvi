@extends('layouts.app')


{{--@section('title', $title)--}}


@section('content')

    <div class="container">

        <form method="post" action="{{ route('event.update', [$event->id, $event->slug]) }}" class="page" enctype="multipart/form-data">
            {{ method_field('PATCH') }} @csrf

            <div class="page-content">
                <div class="page-title">
                    <h2>Upraviť podujatie <a href="{{url()->previous()}}"></a></h2>
                    <a class="btn" href="{{ URL::previous() }}"><i class="fa fa-arrow-left"></i> Späť</a>
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

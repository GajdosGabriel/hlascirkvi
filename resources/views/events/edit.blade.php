@extends('layouts.app')


{{--@section('title', $title)--}}


@section('content')
    @include('organizations._profil-menu')
    <div class="page">

        <form method="post" action="{{ route('event.update', [$event->id, $event->slug]) }}" class="flex" enctype="multipart/form-data">
            {{ method_field('PATCH') }} @csrf

            <div class="page-content m-5">
                <div class="page_title">
                    <h2>Upraviť podujatie <a href="{{url()->previous()}}"></a></h2>
                    <a class="btn" href="{{ URL::previous() }}"><i class="fa fa-arrow-left"></i> Späť</a>
                </div>
                    @include('events._form_a')
            </div>

                <div class="page-aside m-5">
                    @include('events._form_b')

                </div>

        </form>
    </div>
@endsection

@include('posts.editor')

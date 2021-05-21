@extends('layouts.app')


{{--@section('title', $title)--}}


@section('content')
    @include('organizations._profil-menu')
    <div class="page">

        <form method="post" action="{{ route('akcie.update', [$event->id]) }}" class="md:flex"
              enctype="multipart/form-data">
            {{ method_field('PATCH') }} @csrf

            <div class="page-content md:m-5">
                <div class="flex justify-between">
                    <h2 class="page_title text-lg">Upraviť podujatie</h2>
                    <a class="btn" href="{{ URL::previous() }}"><i class="fa fa-arrow-left"></i> Späť</a>
                </div>
                @include('events._form_a')
            </div>

            <div class="page-aside md:m-5">
                @include('events._form_b')

            </div>

        </form>
    </div>
@endsection

@include('posts.editor')

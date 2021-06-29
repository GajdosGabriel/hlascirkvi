@extends('layouts.app')


{{--@section('title', $title)--}}


@section('content')
<div class="grid grid-cols-12 gap-6  ">

    <div class="grid col-span-2  min-h-screen">
        <div class="flex flex-col bg-gray-200">

            @include('profiles._profil-menu')

        </div>
    </div>


    <div class="grid col-span-10 p-5">
        <form method="post" action="{{ route('akcie.store') }}" class="flex" enctype="multipart/form-data">  @csrf

            <div class="page-content">

                @component('layouts.pages.page_title')
                    @slot('title')

                    Nové podujatie

                    @endslot

                    @slot('title_site')

                    <div>
                        <a href="{{ url()->previous() }}" class="btn"> <i class="fa fa-arrow-left"></i> Späť</a>
                    </div>

                    @endslot
                @endcomponent


                    @include('events._form_a')
            </div>

                <div class="page-aside mx-5">
                    @include('events._form_b')

                </div>

        </form>
    </div>
@endsection

@include('posts.editor')

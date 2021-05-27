@extends('layouts.app')

@section('content')
<div class="grid grid-cols-12 gap-6  ">

    <div class="grid col-span-2  min-h-screen">
        <div class="flex flex-col bg-gray-200">

            @include('organizations._profil-menu')

        </div>
    </div>


    <div class="grid col-span-10">
                @include('layouts.errors')
               <h2 class="page_title">Vytvoriť článok</h2>
                <form class="md:w-8/12" method="post" action="{{ route('posts.store') }}" enctype="multipart/form-data">
                    @csrf  @method('POST')
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

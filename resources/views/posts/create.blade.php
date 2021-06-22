@extends('layouts.app')

@section('content')

    @component('layouts.pages.profil')
        @slot('page')
            <div class="grid col-span-10">
                @include('layouts.errors')
                <h2 class="page_title">Vytvoriť článok</h2>
                <form class="md:w-8/12" method="post" action="{{ route('posts.store') }}" enctype="multipart/form-data">
                    @csrf @method('POST')
                    @include('posts.form')
                </form>
            </div>

            <div class="page-aside">
                {{-- aside --}}
            </div>


            </div>

            </div>
            @include('posts.editor')
        @endslot
    @endcomponent

@endsection

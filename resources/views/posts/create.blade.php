@extends('layouts.app')

@section('content')

    @component('layouts.pages.profil')
        @slot('page')
            <div class="grid col-span-10">
                @include('layouts.errors')

                @component('layouts.pages.page_title')
                    @slot('title')

                        Vytvoriť článok

                    @endslot

                    @slot('title_right')

                        <div>
                            <a href="{{ url()->previous() }}" class="btn"> <i class="fa fa-arrow-left"></i> Späť</a>
                        </div>

                    @endslot
                @endcomponent

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

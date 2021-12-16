@extends('layouts.app')

@section('content')

    @component('layouts.components.pages.profil')
        @slot('page')
            <div class="col-span-10 content-start">
                @include('layouts.errors')

                @component('layouts.components.pages.page_title')
                    @slot('title')
                        Vytvoriť článok
                    @endslot

                    @slot('title_right')
                        <a href="{{ url()->previous() }}" class="btn"> <i class="fa fa-arrow-left"></i> Späť</a>
                    @endslot
                @endcomponent

                <form class="md:w-8/12" method="post" action="{{ route('organization.post.store', $organization->id) }}"
                    enctype="multipart/form-data">
                    @csrf @method('POST')
                    @include('posts.form')
                </form>
            </div>
            @include('posts.editor')
        @endslot
    @endcomponent

@endsection

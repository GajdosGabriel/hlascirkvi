@extends('layouts.app')

@section('content')
    <div class="grid grid-cols-12 gap-6  ">


        @include('profiles._profil-menu')


        <div class="col-span-8">


            @component('layouts.pages.page_title')
                @slot('title')
                    Vaše kanály
                @endslot

                @slot('title_right')
                    {{-- // --}}
                @endslot

            @endcomponent

            <div class="md:w-1/3">
                <new-organization />
            </div>

            @include('organizations._organization-table')

            <div class="md:block flex justify-center my-8">
                {{ $organizations->links() }}
            </div>

        </div>

    </div>


@endsection

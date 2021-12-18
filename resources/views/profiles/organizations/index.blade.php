@extends('layouts.app')

@section('content')
    @component('layouts.components.pages.profil')


        @slot('title')
            Vaše kanály
        @endslot

        @slot('title_right')
            {{-- // --}}
        @endslot



        @slot('page')
            <new-organization></new-organization>

            @include('organizations._organization-table')

            <div class="md:block flex justify-center my-8">
                {{ $organizations->links() }}
            </div>


        @endslot
    @endcomponent
@endsection

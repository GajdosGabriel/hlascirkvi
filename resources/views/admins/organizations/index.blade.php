@extends('layouts.app')

@section('content')


    @component('components.pages.admin')

        @slot('title')
            Organizácie
        @endslot

        @slot('title_right')
            {{-- // --}}
        @endslot


        @slot('page')
            <div class="md:w-1/3">
                <new-organization />
            </div>


            @include('organizations._organization-table')


            <div class="md:block flex justify-center my-8">
                {{ $organizations->links() }}
            </div>

        @endslot
    @endcomponent


@endsection

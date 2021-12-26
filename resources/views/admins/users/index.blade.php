@extends('layouts.app')


@section('content')

    @component('components.pages.admin')

        @slot('title')
            Registrovaný užívatelia
        @endslot

        @slot('title_right')

        @endslot



        @slot('page')
            @include('users.users-table')

            <div class="md:block flex justify-center my-8">
                {{ $users->links() }}
            </div>

        @endslot
    @endcomponent


@endsection

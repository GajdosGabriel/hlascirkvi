@extends('layouts.app')
@section('title') <title>{{ 'Registrovaný užívatelia.' }}</title> @endsection

@section('content')

    @component('components.pages.admin')

        @slot('title')
            Registrovaný užívatelia
        @endslot

        @slot('title_right')

        @endslot



        @slot('page')
            @include('users.users_table')

            <div class="md:block flex justify-center my-8">
                {{ $users->links() }}
            </div>

        @endslot
    @endcomponent


@endsection

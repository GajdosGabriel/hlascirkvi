@extends('layouts.app')
@section('title') <title>{{ 'Registrovaný užívatelia.' }}</title> @endsection

@section('content')

    <x-pages.admin>

        <x-slot name="title">
            Registrovaný užívatelia
        </x-slot>

        <x-slot name="title_right">
          {{--  --}}
        </x-slot>



        <x-slot name="page">
            @include('users.users_table')

            <div class="md:block flex justify-center my-8">
                {{ $users->links() }}
            </div>

        </x-slot>
    </x-pages.admin>


@endsection

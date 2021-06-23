@extends('layouts.app')


@section('content')

    @component('layouts.pages.admin')
        @slot('page')

            <div class="col-span-10">

                <div class="page_title flex justify-between">
                    <h2 class="text-2xl">Registrovaný užívatelia</h2>
                </div>

                <div>

                    @include('users.users-table')

                    <div class="md:block flex justify-center my-8">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>

        @endslot
    @endcomponent


@endsection

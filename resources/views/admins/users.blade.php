@extends('layouts.app')


@section('content')

@component('layouts.components.pages.admin')
        @slot('page')

            <div class="col-span-10">

                @component('layouts.components.pages.page_title')
                    @slot('title')

                        Registrovaný užívatelia

                    @endslot

                    @slot('title_right')

                    @endslot
                @endcomponent



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

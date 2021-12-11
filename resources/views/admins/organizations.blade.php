@extends('layouts.app')

@section('content')


    @component('layouts.pages.admin')
        @slot('page')

            <div class="col-span-9">

                @component('layouts.pages.page_title')
                    @slot('title')

                        Organizácie

                    @endslot

                    @slot('title_right')
                    {{-- // --}}
                    @endslot
                @endcomponent

                <div class="w-1/3">
                    <new-organization />
                </div>

                <div>
                    @include('organizations._organization-table')
                </div>

                <div class="md:block flex justify-center my-8">
                    {{ $organizations->links() }}
                </div>
            </div>

        @endslot
    @endcomponent


@endsection

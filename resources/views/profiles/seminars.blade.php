@extends('layouts.app')

@section('content')

    @component('layouts.pages.profil')
        @slot('page')
            <div class="col-span-5 pt-6">


                <div class="flex justify-between mb-6">
                    <h2 class="text-2xl">Semináre panel</h2>
                    <div>
                        <a href="{{ route('seminars.create') }}" class="btn btn-default">Nový semimár</a>
                    </div>
                </div>


                @include('profiles.seminars._list')

            </div>
        @endslot
    @endcomponent


@endsection

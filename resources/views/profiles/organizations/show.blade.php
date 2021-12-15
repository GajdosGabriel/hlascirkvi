@extends('layouts.app')

@section('content')
    <div class="grid grid-cols-12 gap-6  ">

        @include('profiles._profil-menu')


        <div class="col-span-10 content-start">

            @component('layouts.pages.page_title')
                @slot('title')

                    Kanál {{ $organization->title }}

                @endslot

                @slot('title_right')

                @endslot
            @endcomponent


            <h3 class="font-semibold"></h3>

            <h3>Prihlásiť sa do kanálu.</h3>
            <div class="flex mb-7">

                <form action="{{ route('user.update', auth()->id()) }}" method="post" class="mr-4">
                    @method('PUT') @csrf
                    <input type="hidden" name="org_id" value="{{ $organization->id }}" />
                    <button
                        class="px-2 bg-blue-700 text-gray-100 rounded border-2 border-blue-900 hover:bg-blue-600">Nastaviť
                        {{ $organization->title }}
                    </button>
                </form>

                <form method="POST" action="{{ route('organizations.update', $organization->id) }}">
                    @csrf @method('PUT')

                    @if ($organization->published)
                        <button value="0" name="published"
                            class="px-2 bg-green-500 text-gray-100 rounded border-2 border-green-700 hover:bg-green-600">Publikované</button>
                    @else
                        <button value="1" name="published"
                            class="px-2 bg-red-500 text-gray-100 rounded border-2 border-red-700 hover:bg-red-600">Nepublikované</button>
                    @endif
                </form>
            </div>

            <h3>Počet videi: {{ $organization->posts()->count() }}</h3>
            <h3>Nepublikované články: {{ $organization->posts()->count() }}</h3>
            <h3>Zmazané články: </h3>
            <h3>Počet akcii: {{ $organization->events->count() }}</h3>


        </div>

        <div class="page-aside">

        </div>


    </div>

    </div>


@endsection

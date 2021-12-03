@extends('layouts.app')

@section('content')


    <div class="grid grid-cols-12 gap-6  ">



        @include('admins._profil-menu')



        <div class="col-span-5">

            @component('layouts.pages.page_title')
                @slot('title')

                    Updaters panel

                @endslot


            @endcomponent


            @forelse ( $updaters as $tag )

                <div class="flex justify-between p-2 border-b-2 hover:bg-gray-50 border-dashed">
                    <a href="{{ route('updater.organization.index', [$tag->id]) }}">
                        <h4 class="font-semibold text-lg">{{ $tag->id }}. {{ $tag->title }}</h4>
                    </a>
                    <div class="flex">
                        <div class="mr-2">{{ $tag->type }}</div>
                        <div class="font-semibold text-lg">{{ $tag->organizations->count() }}</div>
                    </div>

                    {{-- @can('update', $tag)
                        <tag-dropdown :post="{{ $tag }}" />
                    @endcan --}}
                </div>

            @empty
                žiadne updaters
            @endforelse



        </div>

        <div class="col-span-3">

            <h2 class="page_title">Nový updater</h2>

            @include('admins.updater.form')


        </div>

    </div>



@endsection

@extends('layouts.app')

@section('content')


    <div class="grid grid-cols-12 gap-6  ">

        <div class="grid col-span-2  min-h-screen">
            <div class="flex flex-col bg-gray-200">

                @include('admins._profil-menu')

            </div>
        </div>


        <div class="col-span-5">

            @component('layouts.pages.page_title')
            @slot('title')

            Tags panel

            @endslot


        @endcomponent


            @forelse ( $tags as $tag )

            <div class="flex justify-between mb-4">
                <h4 class="font-semibold text-lg">{{ $tag->title }}</h4>

                @can('update', $tag)
                    <tag-dropdown :post="{{ $tag }}" />
                @endcan
            </div>

            @empty
            žiadne tagy

            @endforelse



        </div>

        <div class="col-span-3">

            <h2 class="page_title">Nový tág</h2>

            @include('tags.form')


        </div>

    </div>



@endsection

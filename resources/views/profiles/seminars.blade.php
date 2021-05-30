@extends('layouts.app')

@section('content')


    <div class="grid grid-cols-12 gap-6  ">

        <div class="grid col-span-2  min-h-screen">
            <div class="flex flex-col bg-gray-200">

                @include('profiles._profil-menu')

            </div>
        </div>


        <div class="col-span-5">


            <h2 class="text-2xl mb-7">Semináre panel</h2>


            @forelse ( $seminars as $seminar )

            <div class="flex justify-between mb-4">
                <h4 class="font-semibold text-lg">{{ $seminar->title }}</h4>

                @can('update', $seminar)
                    <c-article-dropdown :post="{{ $seminar }}" :model="/seminars/" />
                @endcan
            </div>

            @empty
            žiadne semináre

            @endforelse



        </div>

        <div class="col-span-3">

            <h2 class="page_title">Nový seminár</h2>

            <form class="" method="post" action="{{ route('seminars.store') }}">
                @csrf @method('POST')

            @include('seminars.form')

        </form>

        </div>

    </div>



@endsection

@extends('layouts.app')

@section('content')


    <div class="grid grid-cols-12 gap-6  ">

        <div class="grid col-span-2  min-h-screen">
            <div class="flex flex-col bg-gray-200">

                @include('profiles._profil-menu')

            </div>
        </div>


        <div class="col-span-5 pt-6">


            <div class="flex justify-between mb-6">
                <h2 class="text-2xl">Semináre panel</h2>
                <div>
                    <a href="{{ route('seminars.create') }}" class="btn btn-default">Nový semimár</a>
                </div>
            </div>


          @include('seminars._list')

        </div>

        <div class="col-span-3">



        </div>

    </div>



@endsection

@extends('layouts.app')

@section('content')


    <div class="grid grid-cols-12 gap-6  ">

        <div class="grid col-span-2  min-h-screen">
            <div class="flex flex-col bg-gray-200">

                @include('organizations._profil-menu')

            </div>
        </div>


        <div class="grid col-span-10">

            {{-- <user-card :user="{{ $organization }}"></user-card> --}}


        </div>
            <div class="page-content">


                {{-- <div> --}}
                {{-- <form action="{{ route('account.avatar.store') }}" method="post"> @csrf --}}

                {{-- <avatar endpoint="{{ route('account.avatar.store') }}" send-as="image" current-avatar="{{ Auth::user()->avatar  }}"></avatar> --}}

                {{-- <div class="form-group"> --}}
                {{-- <button class="btn btn-small">Uložiť</button> --}}
                {{-- </div> --}}

                {{-- </form> --}}
                {{-- </div> --}}


            </div>

            <div class="">

                @if (auth()->check())
                    <messenger-modul :user="{{ $organization }}" :messages="{{ $messages }}"></messenger-modul>
                @endif


                {{-- <organization-card :user="{{ $organization }}"></organization-card> --}}

                {{-- @if (auth()->check()) --}}
                {{-- <messenger-modul :user="{{ $user }}" :messages="{{ $messages }}"></messenger-modul> --}}
                {{-- @endif --}}

            </div>



    </div>


@endsection

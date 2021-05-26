@extends('layouts.app')

@section('content')
    @include('organizations._profil-menu')
    <div class="page">

        <div class="">

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

            <div class="md:w-4/12">
                <user-card :user="{{ $organization }}"></user-card>

                @if (auth()->check())

                    {{-- @include('admins.statistic-view') --}}
                    <messenger-modul :user="{{ $organization }}" :messages="{{ $messages }}"></messenger-modul>
                @endif


                {{-- <organization-card :user="{{ $organization }}"></organization-card> --}}

                {{-- @if (auth()->check()) --}}
                {{-- <messenger-modul :user="{{ $user }}" :messages="{{ $messages }}"></messenger-modul> --}}
                {{-- @endif --}}

            </div>


        </div>

    </div>


@endsection

@extends('layouts.app')

@section('content')


    <div class="grid grid-cols-12 gap-6  ">



        @include('admins._profil-menu')



        <div class="grid col-span-10">


            <h2 class="text-2xl">Admin panel</h2>

            <ul>
                <li>
                    <a href="{{ route('videa.videa') }}">
                        videa canals
                    </a>
                </li>

                <li>
                    <a href="{{ route('akcie.akcie') }}">
                        Akcie
                    </a>
                </li>

                <li>
                    <a href="{{ route('modlitby.modlitby') }}">
                        Modlitby
                    </a>
                </li>

            </ul>

        </div>




        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>



        {{-- <div> --}}
        {{-- <form action="{{ route('account.avatar.store') }}" method="post"> @csrf --}}

        {{-- <avatar endpoint="{{ route('account.avatar.store') }}" send-as="image" current-avatar="{{ Auth::user()->avatar  }}"></avatar> --}}

        {{-- <div class="form-group"> --}}
        {{-- <button class="btn btn-small">Uložiť</button> --}}
        {{-- </div> --}}

        {{-- </form> --}}
        {{-- </div> --}}


    </div>

    <div class="page-aside">

    </div>


    </div>

    </div>


@endsection

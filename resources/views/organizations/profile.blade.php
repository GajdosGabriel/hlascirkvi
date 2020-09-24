@extends('layouts.app')

@section('content')

    <div class="container">

        <div class="page">

            <div class="page-content">

                <div class="level">
                    <div>
    {{--                    <a class="tag" href="{{ route('organization.profile', [auth()->id(), auth()->user()->slug]) }}">Profil</a>--}}
                        <a class="tag" href="{{ route('organization.index', [ auth()->id(), auth()->user()->slug]) }}">Kanály</a>
                        <a class="tag" href="{{ route('post.create') }}">Nový článok</a>
                        <a class="tag" href="{{ route('event.create') }}">Nové Podujatie</a>
                        <a class="tag" href="{{ route('addresBook.importContacts', [ auth()->id(), auth()->user()->slug] ) }}">Moje kontakty</a>
                    </div>

                        <a href="{{ route('logout') }}"
                           onclick="event.preventDefault();
                        document.getElementById('logout-form').submit();">
                            {{ __('web.Logout') }}
                        </a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>

                </div>



                {{--<div>--}}
                    {{--<form action="{{ route('account.avatar.store') }}" method="post"> @csrf--}}

                        {{--<avatar endpoint="{{ route('account.avatar.store') }}" send-as="image" current-avatar="{{ Auth::user()->avatar  }}"></avatar>--}}

                        {{--<div class="form-group">--}}
                            {{--<button class="btn btn-small">Uložiť</button>--}}
                        {{--</div>--}}

                    {{--</form>--}}
                {{--</div>--}}


            </div>

            <div class="page-aside">
                <user-card :user="{{ $organization }}"></user-card>

                @if(auth()->check())

                    {{--@include('admins.statistic-view')--}}
                    <messenger-modul :user="{{ $organization }}" :messages="{{ $messages }}"></messenger-modul>
                @endif


                {{--<organization-card :user="{{ $organization }}"></organization-card>--}}

                {{--@if(auth()->check())--}}
                    {{--<messenger-modul :user="{{ $user }}" :messages="{{ $messages }}"></messenger-modul>--}}
                {{--@endif--}}

            </div>


        </div>

    </div>


    @endsection
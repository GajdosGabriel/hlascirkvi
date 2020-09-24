@extends('layouts.app')

@section('content')

    <div class="container">

        <div class="page">

            <div class="page-content">


                    <div>
                        @if( auth()->user()->hasRole('admin'))
                            <h5>Admin panel</h5>
                            <a class="tag" href="{{ route('admin.organization.index') }}">Všetky organizácie</a>
                            <a class="tag" href="{{ route('admin.unpublished') }}">Buffer</a>
                            <a class="tag" href="{{ route('admin.statistic', [ 'days' => 1]) }}">Štatistika</a>
                            <a class="tag" href="{{ route('admin.user.index') }}">Užívatelia</a>
                        @endif
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                        </form>
                    </div>
                <div>
                    <h5>Service panel</h5>
                    <a class="tag" href="{{ route('admin.images.index') }}">Images service</a>
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

            </div>


        </div>

    </div>


    @endsection
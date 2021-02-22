@extends('layouts.app')

@section('content')

    <div class="page">

        <div class="page_title">
            <h2 class="text-2xl">Admin panel</h2>
        </div>

            <div class="page-content">


                    <div class="flex space-x-4">
                        @if( auth()->user()->hasRole('admin'))
                            <a class="cursor-pointer hover:text-gray-500 border-2 border-gray-500 hover:bg-blue-200 rounded-md px-2" href="{{ route('admin.organization.index') }}">Všetky organizácie</a>
                            <a class="cursor-pointer hover:text-gray-500 border-2 border-gray-500 hover:bg-blue-200 rounded-md px-2" href="{{ route('admin.unpublished') }}">Buffer</a>
                            <a class="cursor-pointer hover:text-gray-500 border-2 border-gray-500 hover:bg-blue-200 rounded-md px-2" href="{{ route('admin.statistic', [ 'days' => 1]) }}">Štatistika</a>
                            <a class="cursor-pointer hover:text-gray-500 border-2 border-gray-500 hover:bg-blue-200 rounded-md px-2" href="{{ route('admin.user.index') }}">Užívatelia</a>
                        @endif
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                        </form>
                    </div>
                <div>
                    <h5>Service panel</h5>
                    <a class="" href="{{ route('admin.images.index') }}">Images service</a>
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

@extends('layouts.app')
@section('headerCSS')
    <link href="https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css" rel="stylesheet">
@endsection

@section('content')

    <div class="page">


        <div class="page-content">

            <div>
                <a class="tag" href="{{ route('organization.profile', [auth()->id(), auth()->user()->slug]) }}">Späť</a>
            </div>

            @livewire('admin.table.user')

        </div>

        <div class="page-aside">


            {{--                <new-organization :user="{{ auth()->user() }}"></new-organization>--}}

        </div>


    </div>


@endsection

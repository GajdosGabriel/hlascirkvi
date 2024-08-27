@extends('layouts.app')
@section('title')
    <title>{{ 'Všetky články' }}</title>
@endsection

@section('script-header')
    <link rel="stylesheet" href="https://cdn.plyr.io/3.5.3/plyr.css" />
    {{-- <script src='https://www.google.com/recaptcha/api.js?render=6LeQ04YUAAAAAN8P0b0mdmZroMdd4pkL4HbhNfHo'></script> --}}
@endsection

@section('content')
    <x-pages.dashboard-and-aside>

        <x-slot name="title_right">
            <a href="{{ route('profile.post.create') }}" class="btn btn-primary">Nový článok</a>
        </x-slot>


        <x-slot name="page">

            @include('posts.show')

        </x-slot>

        <x-slot name="pageRight">



        </x-slot>

    </x-pages.dashboard-and-aside>
@endsection



@section('script')
    <script src="https://cdn.plyr.io/3.5.3/plyr.js"></script>
    <script defer>
        const player = new Plyr('#player');
    </script>
    <script async defer crossorigin="anonymous"
        src="https://connect.facebook.net/sk_SK/sdk.js#xfbml=1&version=v5.0&appId=500741757380226"></script>
@endsection

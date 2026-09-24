@extends('layouts.app')

@section('content')
<div class="container">

    <div class="page">

        <div class="page-content">
            <youtube-dash user="{{ $user->full_name }}"></youtube-dash>
        </div>

        <div class="page-aside">
            {{--@include('users.user-card')--}}
            <x-front-list-card type="personal" />
            <x-front-list-card type="organization" />
        </div>
    </div>

</div>
@endsection

@extends('layouts.app')
@section('title')
    <title>{{ $post->title }}</title>
@endsection

@section('script-header')
    <link rel="stylesheet" href="https://cdn.plyr.io/3.5.3/plyr.css" />
    {{-- <script src='https://www.google.com/recaptcha/api.js?render=6LeQ04YUAAAAAN8P0b0mdmZroMdd4pkL4HbhNfHo'></script> --}}
@endsection


@section('othermeta')
    <title>{{ $post->title }}</title>
    <meta property="fb:app_id" content="241173683337522" />
    <meta property="og:url" content="{{ $post->routeShow() }}" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="{{ $post->title }}" />
    <meta property="og:description" content="{!! \Illuminate\Support\Str::limit($post->body, 130) !!}" />
    <meta property="og:image"
        content="@forelse($post->images as $image) {{ url($image->originalImageUrl) }}@empty @endforelse" />
    <meta property="og:image:width" content="360" />
    <meta property="og:image:height" content="210" />
    <meta property="og:image:alt" content="{{ $post->title }}" />
@endsection

@section('content')

    <x-pages.page_standart>

        <x-slot name="page">
            @include('events.show_a')
        </x-slot>

        <x-slot name="aside">
            @include('events.show_b')
        </x-slot>
    </x-pages.page_standart>

@endsection


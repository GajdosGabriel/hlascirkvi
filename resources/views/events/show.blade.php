@extends('layouts.app')

@section('title')
    <title>{{ $event->title }}</title>
@endsection


@section('othermeta')
    <title>{{ $event->title }}</title>
    <meta property="fb:app_id" content="241173683337522" />
    <meta property="og:url" content="{{ $event->url['show'] }}" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="{{ $event->title }}" />
    <meta property="og:description" content="{!! \Illuminate\Support\Str::limit($event->body, 130) !!}" />
    <meta property="og:image"
        content="@forelse($event->images as $image)  @if ($loop->first) {{ url($image->originalImageUrl) }} @endif @empty @endforelse" />
    <meta property="og:image:width" content="600" />
    <meta property="og:image:height" content="500" />
    <meta property="og:image:alt" content="{{ $event->title }}" />
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

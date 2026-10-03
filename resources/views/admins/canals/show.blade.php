@extends('layouts.admin')

@section('title')
    <title>{{ 'Kanál ' . $canal->title }}</title>
@endsection

@section('headerCSS')
    @parent
    @include('partials.canal-overview-style')
@endsection

@section('content')
    <x-pages.admin>
        <x-slot name="title">Prehľad kanála</x-slot>
        <x-slot name="page"><x-canal.overview :canal="$canal" /></x-slot>
    </x-pages.admin>
@endsection

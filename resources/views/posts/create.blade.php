@php
    $seo = ['title' => 'Nový článok', 'noindex' => true];
@endphp
@extends('layouts.dashboard')

@section('content')
    <x-dashboard.frame fold>
        <x-dashboard.header heading="Nový článok">
            <x-slot name="lead">Polia označené <span class="ar-req">*</span> sú povinné.</x-slot>
        </x-dashboard.header>

        @include('layouts.errors')

        <form method="post" action="{{ route('profile.posts.store') }}" enctype="multipart/form-data">
            @csrf
            @include('posts.form')
        </form>

        @include('posts.editor')
    </x-dashboard.frame>
@endsection

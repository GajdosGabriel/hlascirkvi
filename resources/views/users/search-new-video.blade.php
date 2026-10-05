@extends('layouts.legacy-page')

@php $seo = ['title' => 'Vyhľadať video', 'noindex' => true]; @endphp

@section('content')
    <div class="ar-document">
        <header class="ar-document__header">
            <p class="ar-kicker">Videotéka</p>
            <h1 class="ar-display">Vyhľadať nové video</h1>
        </header>
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="ar-document__card lg:col-span-2">
                <youtube-dash user="{{ $user->full_name }}"></youtube-dash>
            </div>
            <aside class="ar-aside">
                <x-front-list-card type="personal" />
                <x-front-list-card type="organization" />
            </aside>
        </div>
    </div>
@endsection

@extends('layouts.legacy-page')

@php $seo = ['title' => 'Hlas Cirkvi']; @endphp

@section('content')
    <div class="ar-document">
        <header class="ar-document__header">
            <p class="ar-kicker">Viera a spoločenstvo</p>
            <h1 class="ar-display">Vitajte na Hlase Cirkvi</h1>
            <p class="ar-document__intro">Články, videá a zamyslenia pre každý deň.</p>
        </header>
        <div class="ar-document__card">
            @include('verses.daily-modul')
        </div>
        <a href="{{ url('/') }}" class="ar-btn ar-btn--accent mt-6">Objaviť najnovšie príspevky</a>
    </div>
@endsection

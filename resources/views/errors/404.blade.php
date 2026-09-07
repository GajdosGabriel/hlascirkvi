@extends('layouts.app')

@php
    /* Chybová stránka do vyhľadávača nepatrí ani vtedy, keď na ňu vedie odkaz. */
    $seo = [
        'title' => 'Stránka sa nenašla',
        'noindex' => true,
    ];
@endphp

@section('content')
    <div class="container">
        <div class="page">
            <div class="page-content">
                <h1>Stránka sa nenašla!</h1>
                <p>Chyba 404</p>
                <a href="{{ URL::previous() }}"><button class="btn btn-primary">Vrátiť sa späť</button></a>
            </div>
        </div>
    </div>
@endsection

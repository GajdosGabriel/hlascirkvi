@extends('layouts.app')

@php
    /* Chybová stránka do vyhľadávača nepatrí ani vtedy, keď na ňu vedie odkaz. */
    $seo = [
        'title' => 'Kanál je blokovaný',
        'noindex' => true,
    ];
@endphp

@section('content')

    <div class="container mx-auto">
        <div class="page">
            <div class="page-content">
                <h2>Kanál je blokovaný</h2>
                <p class="font-semibolg text-lg ">{{ $exception->getMessage() }}</p>
                <a href="{{ URL::previous() }}"><button class="btn btn-primary">Vrátiť sa späť</button></a>
            </div>
        </div>
    </div>


    @endsection
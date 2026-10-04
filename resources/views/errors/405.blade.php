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
                <a href="{{ url('/') }}" class="btn btn-primary">Prejsť na úvodnú stránku</a>
            </div>
        </div>
    </div>


    @endsection
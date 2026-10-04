@extends('layouts.app')

@php
    /* Chybová stránka do vyhľadávača nepatrí ani vtedy, keď na ňu vedie odkaz. */
    $seo = [
        'title' => 'Na úkon nie ste autorizovaný',
        'noindex' => true,
    ];
@endphp

@section('content')
    <div class="container">
        <div class="page">
            <div class="page-content">
                <h2>Na úkon nie ste autorizovaný</h2>
                <p>Chyba 403 {{ $exception->getMessage() }}</p>
                <a href="{{ url('/') }}" class="btn btn-primary">Prejsť na úvodnú stránku</a>
            </div>
        </div>
    </div>
@endsection

@extends('layouts.error-page')

@section('error-code', '419')
@section('error-title', 'Platnosť stránky vypršala')
@section('error-description', 'Stránka bola otvorená príliš dlho. Otvorte ju znova a zopakujte úkon. Ak je to potrebné, prihláste sa opäť.')

@section('error-action')
    <a href="{{ url('/login') }}" class="ar-btn ar-btn--quiet">Prihlásiť sa</a>
@endsection

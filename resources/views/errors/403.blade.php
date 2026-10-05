@extends('layouts.error-page')

@section('error-code', '403')
@section('error-title', 'Prístup nie je povolený')
@section('error-description')
    {{ isset($exception) && $exception->getMessage() ? $exception->getMessage() : 'Na zobrazenie tejto stránky alebo vykonanie úkonu nemáte potrebné oprávnenie.' }}
@endsection

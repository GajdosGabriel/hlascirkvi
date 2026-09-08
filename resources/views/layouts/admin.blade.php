@extends('layouts.app')

@section('body-class', 'ar-body ar-admin')

@section('headerCSS')
    @include('partials.dashboard-head')
    @include('partials.admin-system')
@endsection

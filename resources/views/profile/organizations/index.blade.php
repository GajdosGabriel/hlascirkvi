@extends('layouts.app')

@section('title')
    <title>{{ 'Vaše kanály' }}</title>
@endsection

@section('content')
    <x-pages.dashboard-and-aside>
        <x-slot name="title">
            Vaše kanály
        </x-slot>

        <x-slot name="title_right">

            <new-organization></new-organization>

        </x-slot>

        <x-slot name="page">


            @include('organizations._organization-table')

            <div class="md:block flex justify-center my-8">
                {{ $organizations->links() }}
            </div>

        </x-slot>
        </x-pages.dashboard-and-aside>
    @endsection

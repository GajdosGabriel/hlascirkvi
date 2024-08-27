@extends('layouts.app')

@section('title')
    <title>{{ 'Vaše kanály' }}</title>
@endsection

@section('content')
    <x-pages.dashboard>
        <x-slot name="title">
            Vaše kanály
        </x-slot>

        <x-slot name="title_right">
            {{-- // --}}
        </x-slot>

        <x-slot name="page">
            <div class="md:w-1/2">
                <new-organization></new-organization>
            </div>

            @include('organizations._organization-table')

            <div class="md:block flex justify-center my-8">
                {{ $organizations->links() }}
            </div>

        </x-slot>
        </x-pages.admin>
    @endsection

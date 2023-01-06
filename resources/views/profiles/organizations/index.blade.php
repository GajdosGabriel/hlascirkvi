@extends('layouts.app')

@section('title')
    <title>{{ "Vaše kanály" }}</title>
@endsection

@section('content')
    <x-pages.admin>
        <x-slot name="title">
            Vaše kanály
        </x-slot>

        <x-slot name="title_right">
            {{-- // --}}
        </x-slot>



        <x-slot name="page">
            <new-organization></new-organization>

            @include('organizations._organization-table')

            <div class="md:block flex justify-center my-8">
                {{ $organizations->links() }}
            </div>

        </x-slot>
    </x-pages.admin>
@endsection

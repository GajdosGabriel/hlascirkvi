@extends('layouts.admin')

@section('title')
    <title>{{ 'Všetky organizácie' }}</title>
@endsection

@section('content')
    <x-pages.admin>

        <x-slot name="title">
            Kanály
        </x-slot>

        <x-slot name="title_right">
            <new-organization />
        </x-slot>


        <x-slot name="page">
            <div class="md:w-1/3">

            </div>


            <div class="ar-admin__table" role="region" aria-label="Prehľad" tabindex="0">
@include('organizations._organization-table')
</div>


            <div class="md:block flex justify-center my-8">
                {{ $organizations->links() }}
            </div>

        </x-slot>

        </x-pages.admin>
    @endsection

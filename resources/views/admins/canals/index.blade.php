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


            <x-canal.list :canals="$organizations" :admin="true" />


            <div class="md:block flex justify-center my-8">
                {{ $organizations->links() }}
            </div>

        </x-slot>

        </x-pages.admin>
    @endsection

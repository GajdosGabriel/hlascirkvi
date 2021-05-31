@extends('layouts.app')
@section('title') <title>{{ 'Modlitebný múr' }}</title> @endsection
@section('content')

<div class="page">

    <div class="md:grid grid-cols-12 gap-10">

        {{-- Stlpec I. --}}
        <div class="col-span-6">
                <prayers-index-page></prayers-index-page>
            </div>

            <div class="col-span-6">
                <prayers-index-page2></prayers-index-page2>
            </div>

        </div>
    </div>




@endsection

@extends('layouts.app')
@section('title') <title>{{ 'Modlitebný múr' }}</title> @endsection
@section('content')

    <div class="page">
        <div class="flex">

            <div class="md:w-6/12">
                <prayers-index-page></prayers-index-page>
            </div>

        </div>
    </div>




@endsection

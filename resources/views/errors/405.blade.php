@extends('layouts.app')

@section('content')

    <div class="container mx-auto">
        <div class="page">
            <div class="page-content">
                <h2>Kanál je blokovaný</h2>
                <p class="font-semibolg text-lg ">{{ $exception->getMessage() }}</p>
                <a href="{{ URL::previous() }}"><button class="btn btn-primary">Vrátiť sa späť</button></a>
            </div>
        </div>
    </div>


    @endsection
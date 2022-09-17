@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="page">
            <div class="page-content">
                <h1>Stránka sa nenašla!</h1>
                <p>Chyba 500</p>
                <a href="{{ URL::previous() }}"><button class="btn btn-primary">Vrátiť sa späť</button></a>
            </div>
        </div>
    </div>
@endsection

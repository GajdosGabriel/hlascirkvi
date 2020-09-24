@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="page">
            <div class="page-content">
                <h2>Na úkon nie ste autorizovaný</h2>
                <p>Chyba 403 {{ $exception->getMessage() }}</p>
                <a href="{{ URL::previous() }}"><button style="margin-bottom: 20px" class="btn btn-info">Vrátiť sa Späť</button></a>
            </div>
        </div>
    </div>


    @endsection
@extends('layouts.app')
@section('title') <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title> @endsection

@section('content')
<div class="container">
    <div class="page">

        <div class="page-content">
        <h2 class="page-header" style="margin-bottom: 2rem">Vzdelávanie</h2>
        </div>

        <div class="page-aside">
            <div class="card">
                <div class="card-header">
                    Semináre
                </div>
                <div class="card-body">
                    @include('pages.header-nav')
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

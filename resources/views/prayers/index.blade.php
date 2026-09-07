@extends('layouts.app')

@php
    /* Značky pre vyhľadávače a náhľady odkazov skladá partials/meta. */
    $seo = [
        'title' => 'Modlitebný múr',
        'description' => 'Miesto, kde sa dá poprosiť o modlitbu a modliť sa za prosby ostatných. '
            . 'Modlitebný múr Hlasu Cirkvi.',
        'jsonld' => [
            \App\Support\Seo::breadcrumbs([
                ['Hlas Cirkvi', url('/')],
                ['Modlitebný múr', route('modlitby.index')],
            ]),
        ],
    ];
@endphp

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

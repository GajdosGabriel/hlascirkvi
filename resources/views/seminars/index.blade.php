@extends('layouts.app')
@section('title') <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title> @endsection

@section('content')
    <div class="page">

        <div class="page_title">
            <h2 class="text-2xl">
                Vzdelávanie a semináre
            </h2>
        </div>

        <div class="grid grid-cols-12 gap-5">
            @foreach ($tags as $tag)

                <div class="col-span-8">
                    <h4 class="font-semibold text-2xl">{{ $tag->title }}</h4>
                    <c-article-dropdown :post={{ $tag }} :model="/seminars/"></c-article-dropdown>
                </div>
            @endforeach
        </div>



    @endsection

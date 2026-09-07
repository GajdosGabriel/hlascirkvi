@extends('layouts.app')

@php
    /* Značky pre vyhľadávače a náhľady odkazov skladá partials/meta. */
    $seo = [
        'title' => $seminar->title,
        'description' => $seminar->description
            ?: 'Záznamy prednášok zo seminára ' . $seminar->title . ' na Hlase Cirkvi.',
        'canonical' => route('seminars.show', [$seminar->id]),
        'type' => 'article',
        'author' => optional($seminar->organization)->title,
        'jsonld' => [
            \App\Support\Seo::breadcrumbs([
                ['Hlas Cirkvi', url('/')],
                ['Vzdelávanie a kurzy', route('konferencie.pute')],
                [$seminar->title, route('seminars.show', [$seminar->id])],
            ]),
        ],
    ];
@endphp


@section('content')
    <div class="grid grid-cols-12 gap-6 min-h-screen  ">

        <div class="col-span-7 p-5">

            <div class=" flex justify-between">
                <div>
                    <h2 class="text-2xl">
                        <seminar-title :seminar="{{ $seminar }}"></seminar-title>
                    </h2>
                    <seminar-info :seminar="{{ $seminar }}"></seminar-info>
                    <seminar-description :seminar="{{ $seminar }}"></seminar-description>
                </div>

                <div class="flex items-center">
                    @auth
                    <c-article-dropdown :post="{{ $seminar }}" :model="'/seminars/'" :redirect="'seminars'">
                    </c-article-dropdown>
                    @endauth
                </div>
            </div>

            <div class="grid md:grid-cols-3 lg:grid-cols-4 md:gap-7 grid-cols-2 gap-2">
                @forelse($seminar->posts as $post)
                    <card-front :post="{{ $post }}"></card-front>
                    {{-- @include('posts.card-front') --}}
                @empty
                    bez záznamu
                @endforelse
            </div>
        </div>



</div>

    @endsection

@extends('layouts.app')

@php
    /* Značky pre vyhľadávače a náhľady odkazov skladá partials/meta. */
    $seo = [
        'title' => $seminar->title,
        'description' => $seminar->description
            ?: 'Videá a príspevky v kolekcii ' . $seminar->title . ' na Hlase Cirkvi.',
        'canonical' => route('seminars.show', ['seminar' => $seminar->id] + ($posts->currentPage() > 1 ? ['page' => $posts->currentPage()] : [])),
        'noindex' => ! $isPublic,
        'type' => 'article',
        'author' => optional($seminar->canal)->title,
        'jsonld' => [
            \App\Support\Seo::breadcrumbs([
                ['Hlas Cirkvi', url('/')],
                [$seminar->canal->title, route('organizations.show', $seminar->canal_id)],
                [$seminar->title, route('seminars.show', [$seminar->id])],
            ]),
        ],
    ];
@endphp


@section('body-class', 'ar-body')
@section('content')
    <div class="mx-auto max-w-6xl px-4 py-6">
        <div class="mb-4 flex items-start justify-between gap-4">
            <h1 class="ar-display text-2xl font-semibold">{{ $seminar->title }}</h1>
            @include('seminars._actions')
        </div>
        @include('seminars._info')
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($seminar->posts as $post)
                @include('posts.card-front')
            @empty
                <x-dashboard.empty>Táto kolekcia zatiaľ nemá zverejnené príspevky.</x-dashboard.empty>
            @endforelse
        </div>
        <div class="mt-6">{{ $posts->links() }}</div>
    </div>
@endsection

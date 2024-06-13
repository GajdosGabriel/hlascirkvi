@extends('layouts.app')

@section('title')
    <title>{{ $organization->title }}</title>
@endsection

@section('othermeta')
    <meta property="fb:app_id" content="241173683337522" />
    <meta property="og:url" content="https://hlascirkvi.sk" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="{{ $organization->title }}" />
    <meta property="og:description" content="Kázne kresťanskej komunity" />
    <meta property="og:image" content="https://hlascirkvi.sk/images/foto.jpg" />
    <meta property="og:image:width" content="360" />
    <meta property="og:image:height" content="210" />
    <meta property="og:image:alt" content="{{ $organization->title }}" />
@endsection

@section('content')
    <x-pages.page_3_2>

        {{-- Stlpec I. --}}
        <x-slot name="page">

            @if (!empty($organization))
                <organization-page-header :organization="{{ $organization }}"></organization-page-header>
            @endif

            @if (! $organization->published)
           <h2 class="font-semibold text-xl mb-11">Kanál je zrušený</h1>
        @endif

            <div class="grid md:grid-cols-3 lg:grid-cols-4 md:gap-7 grid-cols-2 gap-2">
                @forelse($posts as $post)
                    {{-- <card-front :post="{{ $post }}"></card-front> --}}
                    @include('posts.card-front')
                @empty
                    žiadne príspevky 
                @endforelse
            </div>

            <div class="md:block flex justify-center my-8">
                {{ $posts->links() }}
            </div>

        </x-slot>

        {{-- Stlpec II. --}}

        <x-slot name="aside">
            <div class="col-span-3">

                <comment-card></comment-card>
                @if (isset($post))
                    <x-events.modul-organizationEvents :organization="$post->organization" :post="$post" />
                @endif
                {{-- @include('organizations.list-users') --}}

            </div>

        </x-slot>

        </x-pages.page_title>
    @endsection

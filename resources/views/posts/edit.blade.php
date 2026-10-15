@php
    $seo = ['title' => 'Upraviť článok', 'noindex' => true];
@endphp
@extends('layouts.dashboard')

@section('content')
    <x-dashboard.frame fold>
        <x-dashboard.header heading="Upraviť článok">
            <x-slot name="lead">Polia označené <span class="ar-req">*</span> sú povinné.</x-slot>
            @if ($post->slug)
                <x-slot name="actions">
                    <a href="{{ route('post.show', [$post->id, $post->slug]) }}" class="ar-btn ar-btn--quiet" target="_blank" rel="noopener">
                        <i class="ph ph-arrow-square-out"></i> Zobraziť verejne
                    </a>
                </x-slot>
            @endif
        </x-dashboard.header>

        @include('layouts.errors')

        <form method="POST" action="{{ route('profile.posts.update', $post->id) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            @include('posts.form')
        </form>

        @include('posts.editor')
    </x-dashboard.frame>
@endsection

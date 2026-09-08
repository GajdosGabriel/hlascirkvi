@extends('layouts.app')

@section('title')
    <title>{{ "Články {$organization->title}" }}</title>
@endsection

@section('body-class', 'ar-body')

@section('headerCSS')
    @include('partials.dashboard-head')
@endsection

@section('content')

    @php
        $plural = fn (int $n, string $one, string $few, string $many)
            => $n === 1 ? $one : ($n >= 2 && $n <= 4 ? $few : $many);

        $total = $posts->total();

        // Zapnutý filter mení význam čísla nad výpisom: bez neho ide o archív
        // kanála, s ním o výsledok výberu.
        $filtered = request()->hasAny(['search', 'unpublished', 'deletedAt', 'videoAvailable']);
    @endphp

    <x-dashboard.shell :organization="$organization" section="posts" heading="Články">

        <x-slot name="lead">
            {{ number_format($total, 0, ',', ' ') }}
            {{ $plural($total, 'článok', 'články', 'článkov') }}
            {{ $filtered ? 'vo výbere' : 'v archíve kanála' }}
        </x-slot>

        <x-slot name="actions">
            <a href="{{ route('profile.organization.post.create', $organization->id) }}" class="ar-btn ar-btn--accent">
                <i class="fas fa-plus"></i> Nový článok
            </a>
            <a href="{{ route('organizations.show', $organization->id) }}" class="ar-btn ar-btn--quiet">
                <i class="far fa-eye"></i> Verejný profil
            </a>
        </x-slot>

        {{-- Prepínače zodpovedajú filtrom v App\Filters\PostFilters; lišta ich
             posiela v query stringu, takže sa dajú aj kombinovať. --}}
        <x-filters.bar class="mb-5"
                       :filters="['unpublished' => 'Čaká v bufferi', 'deletedAt' => 'V koši', 'videoAvailable']"
                       search="Hľadať v článkoch" />

        <section class="ar-panel">
            <header class="ar-panel__head">
                <h2 class="ar-panel__title">Články kanála</h2>
                <span class="ar-panel__note">
                    @if ($posts->hasPages())
                        strana {{ $posts->currentPage() }} z {{ $posts->lastPage() }}
                    @endif
                </span>
            </header>

            @forelse ($posts as $post)
                @include('profiles.posts._row')
            @empty
                <p class="ar-empty">
                    @if ($filtered)
                        Výberu nezodpovedá žiadny článok.
                    @else
                        Kanál zatiaľ nemá žiadny článok.
                    @endif
                </p>
            @endforelse
        </section>

        @if ($posts->hasPages())
            <div class="mt-8">
                {{ $posts->links() }}
            </div>
        @endif

    </x-dashboard.shell>
@endsection

@extends('layouts.dashboard')

@section('title')
    <title>{{ "Články {$canal->title}" }}</title>
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

    <x-dashboard.shell :canal="$canal" section="posts" heading="Články">

        <x-slot name="lead">
            {{ number_format($total, 0, ',', ' ') }}
            {{ $plural($total, 'článok', 'články', 'článkov') }}
            {{ $filtered ? 'vo výbere' : 'v archíve kanála' }}
        </x-slot>

        <x-slot name="actions">
            <a href="{{ route('profile.posts.create') }}" class="ar-btn ar-btn--accent">
                <i class="fas fa-plus"></i> Nový článok
            </a>
            <a href="{{ route('organizations.show', $canal->id) }}" class="ar-btn ar-btn--quiet">
                <i class="far fa-eye"></i> Verejný profil
            </a>
        </x-slot>

        {{-- Prepínače zodpovedajú filtrom v App\Filters\PostFilters; lišta ich
             posiela v query stringu, takže sa dajú aj kombinovať. --}}
        <x-filters.bar class="mb-5"
                       :filters="['unpublished' => 'Čaká v bufferi', 'deletedAt' => 'V koši', 'videoAvailable']"
                       search="Hľadať v článkoch" />

        <x-dashboard.panel title="Články kanála" flush>
<x-slot name="note">
                    @if ($posts->hasPages())
                        strana {{ $posts->currentPage() }} z {{ $posts->lastPage() }}
                    @endif
                </x-slot>

            @forelse ($posts as $post)
                @include('profiles.posts._row')
            @empty
                <x-dashboard.empty>
                    @if ($filtered)
                        Výberu nezodpovedá žiadny článok.
                    @else
                        Kanál zatiaľ nemá žiadny článok.
                    @endif
                </x-dashboard.empty>
            @endforelse
        </x-dashboard.panel>

        @if ($posts->hasPages())
            <div class="mt-8">
                {{ $posts->links() }}
            </div>
        @endif

    </x-dashboard.shell>
@endsection

@extends('layouts.admin')

@php
    $seo = ['title' => 'Štatistika návštev · Administrácia', 'noindex' => true];
@endphp

@section('content')
    <x-pages.admin>

        <x-slot name="title">
            Štatistika návštev
        </x-slot>

        <x-slot name="lead">Unikátne návštevy článkov za zvolené obdobie.</x-slot>


        <x-slot name="page">
            @php
                $periods = [1 => 'Dnes', 2 => '2 dni', 7 => 'Týždeň', 14 => '2 týždne', 30 => '30 dní', 90 => '90 dní'];
                $averageViews = $totalPosts > 0 ? round($totalUniqueViews / $totalPosts, 1) : 0;
                $maxViews = max((int) ($topPost->unique_view ?? 0), 1);
            @endphp

            {{-- Obdobie rovnakými prepínačmi ako filtre v ostatných výpisoch. --}}
            <nav class="mb-6 flex flex-wrap gap-2" aria-label="Obdobie štatistiky">
                @foreach ($periods as $periodDays => $periodLabel)
                    <a href="{{ route('admin.statistic.index', ['lastDays' => $periodDays]) }}"
                       @class(['ar-tab', 'ar-tab--on' => $days === $periodDays])
                       @if ($days === $periodDays) aria-current="page" @endif>{{ $periodLabel }}</a>
                @endforeach
            </nav>

            <div class="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <x-dashboard.metric class="ar-kpi--lead" label="Unikátne návštevy" :value="number_format($totalUniqueViews, 0, ',', ' ')">spolu za obdobie</x-dashboard.metric>
                <x-dashboard.metric label="Čítané články" :value="number_format($totalPosts, 0, ',', ' ')">článkov s návštevou</x-dashboard.metric>
                <x-dashboard.metric label="Priemer na článok" :value="number_format($averageViews, 1, ',', ' ')">unikátnej návštevy</x-dashboard.metric>
                <x-dashboard.metric label="Najúspešnejší článok" :value="number_format($topPost->unique_view ?? 0, 0, ',', ' ')">{{ $topPost ? Str::limit($topPost->title, 32) : 'bez dát' }}</x-dashboard.metric>
            </div>

            <x-dashboard.panel title="Najčítanejšie články" flush>
                <x-slot name="note">{{ number_format($totalPosts, 0, ',', ' ') }} záznamov</x-slot>
            <x-dashboard.table class="rounded-none border-0" label="Štatistiky článkov">
                <thead>
                    <tr>
                        <th class="w-14 text-center">#</th>
                        <th>Článok</th>
                        <th>Kanál</th>
                        <th class="text-right">Za obdobie</th>
                        <th class="text-right">Celkovo</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($posts as $post)
                        <tr>
                            <td class="text-center font-semibold text-[color:var(--ar-muted)]">{{ $posts->firstItem() + $loop->index }}</td>
                            <td>
                                <a href="{{ route('post.show', [$post->id, $post->slug]) }}" class="font-semibold text-[color:var(--ar-ink)] transition-colors hover:text-[color:var(--ar-accent)]">
                                    {{ Str::limit($post->title, 60) }}
                                </a>
                                <div class="mt-2 h-1.5 max-w-sm overflow-hidden rounded-full bg-[color:var(--ar-paper-deep)]" aria-hidden="true">
                                    <span class="block h-full rounded-full bg-[color:var(--ar-accent)]" style="width: {{ round(($post->unique_view / $maxViews) * 100, 1) }}%"></span>
                                </div>
                            </td>
                            <td class="text-[color:var(--ar-ink-soft)]">{{ $post->canal }}</td>
                            <td class="text-right font-bold tabular-nums text-[color:var(--ar-accent)]">{{ number_format($post->unique_view, 0, ',', ' ') }}</td>
                            <td class="text-right tabular-nums text-[color:var(--ar-ink-soft)]">{{ number_format($post->count_view, 0, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="py-10 text-center">
                                    <p class="font-semibold text-[color:var(--ar-ink)]">Zatiaľ bez návštev</p>
                                    <p class="mt-1 text-xs text-[color:var(--ar-ink-soft)]">Skúste zvoliť dlhšie obdobie.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </x-dashboard.table>
            </x-dashboard.panel>

            @if ($posts->hasPages())
                <div class="mt-8">{{ $posts->links() }}</div>
            @endif

        </x-slot>

        </x-pages.admin>
    @endsection

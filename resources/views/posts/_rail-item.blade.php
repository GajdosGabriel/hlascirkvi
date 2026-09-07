@php
    /*
     * Karta v páse archívu. Stojí zvlášť, lebo ju vykresľuje aj šablóna
     * detailu pri načítaní stránky, aj JSON odpoveď pri doťahovaní ďalšej
     * dávky — jedna predloha, jeden vzhľad.
     */
    $itemUrl  = route('post.show', [$item->id, $item->slug]);
    // Prenosy prídu z YouTube s nulovou dĺžkou; "0:00" na karte nič nehovorí.
    $duration = $item->video_duration === '0:00' ? null : $item->video_duration;
@endphp

<a href="{{ $itemUrl }}" title="{{ $item->title }}"
   class="ar-card group block overflow-hidden rounded-lg">

    <span class="relative block overflow-hidden">
        @include('partials.thumb', [
            'model' => $item,
            'alt' => $item->title,
            'class' => 'ar-thumb',
        ])

        @if ($duration)
            <span class="absolute bottom-1.5 right-1.5 rounded bg-black/75 px-1.5 py-0.5 text-[.65rem] font-medium tabular-nums text-white">
                {{ $duration }}
            </span>
        @endif
    </span>

    <span class="block p-3">
        <span class="ar-display block text-[.8rem] font-semibold leading-snug transition-colors group-hover:text-[color:var(--ar-accent)] md:text-sm">
            <span class="ar-clamp-2">{{ $item->title }}</span>
        </span>
        <time datetime="{{ $item->created_at->toIso8601String() }}"
              class="mt-1.5 block text-xs text-gray-400">
            {{ $item->created_at->locale('sk')->isoFormat('D. M. YYYY') }}
        </time>
    </span>
</a>

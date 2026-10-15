@php
    $postUrl         = route('post.show', [$post->id, $post->slug]);
    $canalUrl = route('organizations.show', [$post->canal->id]);

    // `favorites` je na modeli v $with, takže sa tu nedopytujeme databázy
    // pre každú kartu zvlášť — na to doplácal pôvodný favorites()->exists().
    $isRecommended = $post->favorites->isNotEmpty();

    // Prenosy prídu z YouTube s nulovou dĺžkou; "0:00" na karte nič nehovorí.
    $duration = $post->video_duration === '0:00' ? null : $post->video_duration;
@endphp

<article class="ar-card group flex h-full flex-col overflow-hidden rounded-lg">

    {{-- Náhľad so štítkami --}}
    {{-- Náhľad vedie na ten istý príspevok ako titulok pod ním, preto je
         pre čítačky aj klávesnicu skrytý a obrázok má prázdny alt — inak by
         čítačka názov prečítala dvakrát a Tab by na karte zastal dvakrát.
         Prázdny alt navyše nevypíše text do sivého boxu, kým sa obrázok načíta. --}}
    <a href="{{ $postUrl }}" class="relative block overflow-hidden" tabindex="-1" aria-hidden="true">
        @include('partials.thumb', [
            'model' => $post,
            'alt' => '',
            'class' => 'ar-thumb',
        ])

        @if ($isRecommended)
            <span class="absolute left-2 top-2 whitespace-nowrap rounded-full bg-white/95 px-2 py-0.5 text-[.62rem] font-bold uppercase tracking-wide text-[color:var(--ar-accent)] shadow-sm">
                <i class="ph-fill ph-thumbs-up mr-0.5"></i> Odporúčané
            </span>
        @endif

        @if ($duration)
            <span class="absolute bottom-2 right-2 rounded bg-black/75 px-1.5 py-0.5 text-[.65rem] font-medium tabular-nums text-white">
                {{ $duration }}
            </span>
        @endif
    </a>

    {{-- Text karty --}}
    <div class="flex flex-1 flex-col p-3">
        <h2 class="contents">
            <a href="{{ $postUrl }}" title="{{ $post->title }}"
               class="ar-display block text-[.8rem] font-semibold leading-snug transition-colors group-hover:text-[color:var(--ar-accent)] md:text-sm">
                <span class="ar-clamp-3">{{ $post->title }}</span>
            </a>
        </h2>

        {{-- py-1 dvíha odkaz kanála na 24 px, aby sa naň dalo trafiť prstom. --}}
        <div class="mt-auto pt-2 text-xs">
            <a href="{{ $canalUrl }}" class="ar-link inline-block max-w-full truncate py-1 align-bottom font-medium text-gray-600 hover:text-[color:var(--ar-accent)]">
                {{ $post->canal->title }}
            </a>
            <time datetime="{{ $post->created_at->toIso8601String() }}" class="block text-gray-500">
                {{ $post->dateForHumans }}
            </time>
        </div>

    </div>
</article>

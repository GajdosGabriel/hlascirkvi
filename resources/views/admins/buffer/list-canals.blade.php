{{-- Panel sa volal „Kresťanské osobnosti", čo je názov predného zoznamu na
     úvodnej stránke — s ním nemá nič spoločné. Sú to kanály, ktorým niečo
     čaká vo fronte. --}}
<x-dashboard.panel title="Kanály s čakajúcimi príspevkami" class="mt-5">

        {{-- Počet čakajúcich príspevkov ráta withCount v BufferController,
             takže tu už nič nedopytujeme. --}}
        @forelse ($canals as $canal)
            <ul>
                <li class="flex justify-between">
                    <a href="?posts={{ $canal->id }}">{{ $canal->title }}</a>
                    <span style="margin-left: 5rem">({{ $canal->unpublished_posts_count }})</span>
                </li>
            </ul>
        @empty
            bez záznamu
        @endforelse
    </x-dashboard.panel>

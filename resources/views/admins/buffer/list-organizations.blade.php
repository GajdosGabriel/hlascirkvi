<section class="card">
    <header class="card_header">Kresťanské osobnosti</header>
    <div class="card_body">

        {{-- Počet čakajúcich príspevkov ráta withCount v BufferController,
             takže tu už nič nedopytujeme. --}}
        @forelse ($organizations as $organization)
            <ul>
                <li class="flex justify-between">
                    <a href="?posts={{ $organization->id }}">{{ $organization->title }}</a>
                    <span style="margin-left: 5rem">({{ $organization->unpublished_posts_count }})</span>
                </li>
            </ul>
        @empty
            bez záznamu
        @endforelse
    </div>

</section>

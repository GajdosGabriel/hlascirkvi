<div class="p-3 mb-10 bg-gray-200">
    @if (request()->is('/'))

        <div class="page_title">

            <div>
                <h2 class="font-semibold md:text-2xl ">Sviatok Turice</h2>
                <p>Špeciálna príloha o Duchu Svätom na sviatok Turíc, nedeľa, 23. mája 2021</p>
            </div>

            {{-- <div>
                <a title="Doporučené našími čitateľmi" href="?posts=recomended" style="margin: .5rem"><i
                        class="fas fa-thumbs-up"></i></a>
                <a title="Najsledovanejšie videa za dva týždne" href="?posts=trends" style="margin: .5rem"><i
                        class="fas fa-sort-amount-up"></i></a>
                <a title="Videa podľa počtu zobrazení" href="?posts=mostVisited"><i class="far fa-eye"></i></a>
            </div> --}}
        </div>
    @else
        @if (!empty($organization))
            <organization-page-header :organization="{{ $organization }}">
            </organization-page-header>
        @endif
    @endif


    <div class="grid md:grid-cols-3 lg:grid-cols-5 md:gap-7 grid-cols-2 gap-2">
        @forelse($videos as $post)

            <post-card :post="{{ $post }}"></post-card>
            {{-- @include('posts.post-card') --}}
        @empty
            bez záznamu
        @endforelse
    </div>

    {{-- <div class="md:block flex justify-center mt-4">
        {{ $posts->links() }}
    </div> --}}
</div>

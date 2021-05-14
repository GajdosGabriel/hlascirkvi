@if (request()->is('/'))
    <div class="mb-10 bg-gray-200 rounded-md">


        <div class="page_title px-3 pt-1 pb-2 rounded-t-md bg-yellow-500 shadow-md">

            <div class="text-gray-100">
                <h2 class="font-semibold md:text-2xl ">Sviatok Turíce</h2>
                <p class="text-gray-800 ">Špeciálna príloha o Duchu Svätom na sviatok Turíc v nedeľu, 23. mája 2021</p>
            </div>

        </div>



        <div class="grid md:grid-cols-3 lg:grid-cols-5 md:gap-5 grid-cols-2 gap-2 p-3">
            @forelse($videos as $post)

                <post-card :post="{{ $post }}" :createdat="{{ json_encode(false) }}"
                    :shortertext="{{ json_encode(false) }}"></post-card>
                {{-- @include('posts.post-card') --}}
            @empty
                bez záznamu
            @endforelse
        </div>

        {{-- <div class="md:block flex justify-center mt-4">
        {{ $posts->links() }}
    </div> --}}
    </div>
@endif

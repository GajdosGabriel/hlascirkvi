{{ $page_full  ?? null }}
<div class="page md:grid grid-cols-12 gap-10">

    <div class="col-span-8">

        {{-- <div class="flex justify-between mb-6 mt-6">
            <h1 class="text-2xl font-semibold">{{ $title }}</h1>
        
            <div>
                {{ $title_right ?? null }}
            </div>
        
        </div> --}}


        {{ $page }}

    </div>

    <div class="col-span-3">


        {{ $aside }}

    </div>
</div>

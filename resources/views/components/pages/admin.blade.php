<div class="md:grid grid-cols-12 gap-6">

    <div class="grid col-span-2  min-h-screen">
        <div class="flex flex-col bg-gray-200">
            
            @include($typeMenu())

        </div>
    </div>

    <div class="col-span-8">

        <div class="flex justify-between mb-6 mt-6">
            <h1 class="text-2xl font-semibold">{{ $title }}</h1>

            <div>
                {{ $title_right ?? null }}
            </div>

        </div>


        {{ $page }}


    </div>
</div>

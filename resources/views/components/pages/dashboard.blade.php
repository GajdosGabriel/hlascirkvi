<div class="md:grid grid-cols-12 gap-6">

    <div class="grid col-span-2">
        <div class="flex flex-col bg-gray-200">

            <x-navigation.aside-menu></x-navigation.aside-menu>

        </div>
    </div>

    <div class="col-span-8">

        <div class="flex justify-between mb-6 mt-6">
            <h1 class="text-2xl font-semibold">{{ $title }}</h1>

            <div class="flex">
                {{ $title_right ?? null }}
            </div>

        </div>
        
        <x-filters.card />

        {{ $page }}


    </div>
</div>

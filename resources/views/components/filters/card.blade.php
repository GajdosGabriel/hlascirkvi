@if ($showComponent())
    <div class="border-2 border-gray-300 rounded-sm p-4 flex justify-between mb-4 items-center">
        <div class="flex space-x-3">
            {{-- Toto volá class --}}
            <x-filters.unpublished></x-filters.unpublished>
            <x-filters.banned></x-filters.banned>
            <x-filters.fulfilled></x-filters.fulfilled>
        </div>

        <div>
            <x-search-form />
        </div>
    </div>
@endif

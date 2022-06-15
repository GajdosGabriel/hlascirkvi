@if($showComponent())
    <div class="border-2 border-gray-300 rounded-sm p-4 flex justify-between mb-4 items-center">
        <div class="flex space-x-3">
            {{ $left }}
        </div>

        <div>
            {{ $right ?? null }}
        </div>
    </div>
@endif

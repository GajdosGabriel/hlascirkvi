{{--
    Obsah detailu kanála, spoločný pre správcu (dashboard/canals/{id})
    aj administrátora (admin/canal/{id}). Panely, ktoré patria len jednej
    strane, sa posielajú cez slot.
--}}
@props(['canal'])

<div class="grid gap-5 md:grid-cols-2">

    <div class="ar-panel p-5">
        <x-canal.statistic :canal="$canal" />
    </div>

    {{ $slot }}

</div>

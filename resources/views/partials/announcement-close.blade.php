{{-- Zatváracie tlačidlo obrázkového baneru; obsluhuje ho skript
     v resources/views/components/announcements.blade.php. --}}
@if ($item->dismissible)
    <button type="button" data-announcement-close
            class="absolute top-1 right-1 flex h-6 w-6 items-center justify-center rounded-full bg-black/55 text-base leading-none text-white opacity-80 hover:opacity-100"
            title="Zavrieť oznam" aria-label="Zavrieť oznam">&times;</button>
@endif

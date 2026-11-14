{{-- Obrázok baneru z oznamu (resources/views/components/announcements.blade.php).
     Súbor sa servíruje tak, ako bol nahratý, takže animovaný GIF/WebP ostane
     animovaný. width/height držia miesto, aby stránka pri načítaní neposkočila.
     Odkaz vedie cez počítadlo preklikov (Announcement::clickUrl()). --}}
@php
    $picture = view('partials.announcement-picture', ['item' => $item, 'eager' => $eager ?? false]);
@endphp

@if ($item->clickUrl())
    <a href="{{ $item->clickUrl() }}" rel="nofollow{{ $item->linkIsExternal() ? ' noopener' : '' }}"
       @if ($item->linkIsExternal()) target="_blank" @endif
       class="block max-w-full transition-opacity hover:opacity-90"
       title="{{ $item->link_text ?: $item->title }}">{!! $picture->render() !!}</a>
@else
    {!! $picture->render() !!}
@endif

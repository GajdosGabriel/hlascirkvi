@props(['items', 'title'])

{{-- Rebríček najsledovanejších príspevkov kanála.

     Blok bol znak po znaku rovnaký v posts/show.blade.php aj
     v organizations/index.blade.php, líšil sa len nadpisom. --}}
@if ($items->isNotEmpty())
    <section class="rounded-lg border border-[color:var(--ar-line)] bg-white p-4">
        <h2 class="mb-3 text-xs font-bold uppercase tracking-wider text-gray-400">
            {{ $title }}
        </h2>
        <ol class="space-y-3">
            @foreach ($items as $index => $item)
                <li>
                    <a href="{{ route('post.show', [$item->id, $item->slug]) }}"
                       title="{{ $item->title }}" class="group flex gap-3">
                        <span class="ar-rank {{ $index === 0 ? 'ar-rank--first' : '' }}">
                            {{ $index + 1 }}
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="ar-clamp-2 text-sm leading-snug transition-colors group-hover:text-[color:var(--ar-accent)]">
                                {{ $item->title }}
                            </span>
                            <span class="mt-1 block text-xs text-gray-400">
                                <i class="far fa-eye mr-1"></i>{{ number_format((int) $item->count_view, 0, ',', ' ') }}
                            </span>
                        </span>
                    </a>
                </li>
            @endforeach
        </ol>
    </section>
@endif

<picture class="block max-w-full">
    @if ($item->mobileImageUrl())
        <source media="(max-width: 639px)" srcset="{{ $item->mobileImageUrl() }}"
                @if ($item->mobile_image_width) width="{{ $item->mobile_image_width }}" height="{{ $item->mobile_image_height }}" @endif>
    @endif
    <img src="{{ $item->imageUrl() }}" alt="{{ $item->title }}"
         @if ($item->image_width) width="{{ $item->image_width }}" height="{{ $item->image_height }}" @endif
         loading="{{ $eager ? 'eager' : 'lazy' }}" decoding="async"
         class="block h-auto max-w-full rounded-sm">
</picture>

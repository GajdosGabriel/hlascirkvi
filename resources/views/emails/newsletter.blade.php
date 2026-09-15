<x-mail::message :unsubscribe-url="$unsubscribeUrl">
{{--
    Obsah ide cez Markdown. HTML bloky preto stoja od začiatku riadku a bez
    prázdnych riadkov vnútri (ani Blade komentárov, tie po sebe prázdny
    riadok nechajú) — inak by ich parser ukončil a zvyšok vypísal ako text.
--}}
<h1 class="digest-title">Novinky z portálu</h1>
<p class="digest-lead">Najsledovanejšie videá a modlitby, ku ktorým sa môžete pripojiť.</p>

@if ($posts->isNotEmpty())
<h2 class="digest-heading">Najsledovanejšie videá</h2>
@foreach ($posts as $post)
@php
    $thumb = $post->images->first()?->ThumbImageUrl;
    $thumb = $thumb ? url($thumb) : asset('images/foto.jpg');
    $link = route('post.show', [$post->id, $post->slug]);
@endphp
<table class="digest-item" width="100%" cellpadding="0" cellspacing="0" role="presentation"><tr>
<td class="digest-thumb" width="120"><a href="{{ $link }}"><img src="{{ $thumb }}" width="120" alt=""></a></td>
<td class="digest-text"><a class="digest-link" href="{{ $link }}">{{ $post->title }}</a><p class="digest-meta">{{ $post->organization?->title }}</p></td>
</tr></table>
@endforeach
@endif

@if ($prayers->isNotEmpty())
<h2 class="digest-heading">Nové modlitby</h2>
@foreach ($prayers as $prayer)
<table class="digest-item" width="100%" cellpadding="0" cellspacing="0" role="presentation"><tr>
<td class="digest-text"><a class="digest-link" href="{{ route('modlitby.index') }}">{{ $prayer->title }}</a><p class="digest-meta">{{ \Illuminate\Support\Str::limit(strip_tags((string) $prayer->body), 160) }}</p></td>
</tr></table>
@endforeach
@endif

<x-mail::button :url="config('app.url')">
Prejsť na portál
</x-mail::button>

S pozdravom,<br>
tím HlasCirkvi.sk
</x-mail::message>

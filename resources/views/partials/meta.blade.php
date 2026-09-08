{{-- Značky pre vyhľadávače a náhľady odkazov na sieťach.

     Vkladajú ju všetky layouty verejnej časti. Šablóna stránky si pred
     @extends pripraví pole $seo (title, description, image, type, canonical,
     jsonld, …); čo v ňom nie je, doplní App\Support\Seo z config/seo.php.

     Staršie stránky, ktoré si titulok stále píšu cez @section('title'),
     ostávajú funkčné — ich sekcia má prednosť. --}}

@php
    $meta = \App\Support\Seo::resolve($seo ?? []);
@endphp

@hasSection('title')
    @yield('title')
@else
    <title>{{ $meta['document_title'] }}</title>
@endif

<meta name="description" content="{{ $meta['description'] }}">
<meta name="robots" content="{{ $meta['robots'] }}">
<link rel="canonical" href="{{ $meta['canonical'] }}">
@if ($meta['prev'])
    <link rel="prev" href="{{ $meta['prev'] }}">
@endif
@if ($meta['next'])
    <link rel="next" href="{{ $meta['next'] }}">
@endif

{{-- Open Graph — číta ho Facebook, Messenger, WhatsApp, LinkedIn aj Viber. --}}
<meta property="og:site_name" content="{{ $meta['site_name'] }}">
<meta property="og:locale" content="{{ $meta['locale'] }}">
<meta property="og:type" content="{{ $meta['type'] }}">
<meta property="og:url" content="{{ $meta['canonical'] }}">
<meta property="og:title" content="{{ $meta['title'] }}">
<meta property="og:description" content="{{ $meta['og_description'] }}">
<meta property="og:image" content="{{ $meta['image'] }}">
<meta property="og:image:alt" content="{{ $meta['image_alt'] }}">
@if ($meta['image_type'])
    <meta property="og:image:type" content="{{ $meta['image_type'] }}">
@endif
@if ($meta['image_width'] && $meta['image_height'])
    <meta property="og:image:width" content="{{ $meta['image_width'] }}">
    <meta property="og:image:height" content="{{ $meta['image_height'] }}">
@endif
@if ($meta['facebook_app_id'])
    <meta property="fb:app_id" content="{{ $meta['facebook_app_id'] }}">
@endif

@if ($meta['type'] === 'article')
    @if ($meta['published'])
        <meta property="article:published_time" content="{{ $meta['published'] }}">
    @endif
    @if ($meta['modified'])
        <meta property="article:modified_time" content="{{ $meta['modified'] }}">
    @endif
    @if ($meta['author'])
        <meta property="article:author" content="{{ $meta['author'] }}">
    @endif
    @if ($meta['section'])
        <meta property="article:section" content="{{ $meta['section'] }}">
    @endif
    @foreach ($meta['tags'] as $tag)
        <meta property="article:tag" content="{{ $tag }}">
    @endforeach
@endif

@if ($meta['video'])
    {{-- Náhľad s prehrávateľným videom priamo v príspevku na Facebooku. --}}
    <meta property="og:video" content="{{ $meta['video']['url'] }}">
    <meta property="og:video:secure_url" content="{{ $meta['video']['url'] }}">
    <meta property="og:video:type" content="text/html">
    <meta property="og:video:width" content="{{ $meta['video']['width'] ?? 1280 }}">
    <meta property="og:video:height" content="{{ $meta['video']['height'] ?? 720 }}">
@endif

{{-- X (Twitter) si berie chýbajúce hodnoty z og:, dopĺňame len to vlastné.
     Kartu typu player X povoľuje až po schválení domény, preto ostávame pri
     obrázkovej — inak by sa náhľad nevykreslil vôbec. --}}
<meta name="twitter:card" content="{{ $meta['twitter_card'] }}">
@if ($meta['twitter_site'])
    <meta name="twitter:site" content="{{ $meta['twitter_site'] }}">
@endif

{{-- SVG berú súčasné prehliadače, .ico ostáva pre staršie a pre /favicon.ico,
     ktoré si niektoré čítačky ťahajú bez ohľadu na značky v hlavičke. --}}
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
<meta name="theme-color" content="{{ config('seo.theme_color') }}">

@foreach ($meta['jsonld'] as $schema)
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endforeach

{{-- Priestor pre stránky, ktoré potrebujú vlastnú značku navyše. --}}
@yield('meta')
@yield('othermeta')

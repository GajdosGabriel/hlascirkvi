<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.meta')

            {{--Editor--}}
    @yield('script-header')


    <!-- Fonts -->
    <link href='https://fonts.googleapis.com/css?family=Roboto:300,400,600' rel='stylesheet' type='text/css'>
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">

    {{-- https://github.com/aFarkas/lazysizes--}}
    <script src="{{ asset('js/lazysizes.min.js') }}" async=""></script>
    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('partials.design-system')

    @yield('headerCSS')




    <script nonce="{{ csp_nonce() }}">
        window.App = {!! json_encode([
            'csrfToken' => csrf_token(),
                'user' => Auth::user(),
            'signedIn' => Auth::check(),
             'humanStamp' => Auth::guest() ? \App\Support\HumanCheck::stamp() : null,
             'baseUrl' => asset('/')
        ]) !!};
    </script>



</head>
{{-- Stránky, ktoré už nosia nový vzhľad, si sem doplnia `ar-body`; ostatné
     ostávajú na pôvodnom bielom podklade. --}}
<body class="@yield('body-class')">
    <a href="#obsah" class="sr-only focus:not-sr-only focus:fixed focus:left-2 focus:top-2 focus:z-[100] focus:rounded focus:bg-white focus:px-3 focus:py-2 focus:text-sm focus:font-semibold focus:text-blue-900 focus:shadow-lg">Preskočiť na obsah</a>

    @can('admin')
    {{-- Admin nesleduje smartlook a google statistick --}}
    @else
        @include('partials.analyticstracking')
    @endcan


    <div id="app">
        <x-announcements placement="above_menu" />
        <x-navigation.main-menu/>

        {{-- Oznamy správcu webu. Sú rovnaké aj v layouts/article — ten stojí
             mimo tohto layoutu a bez toho by ich detail príspevku nemal. --}}
        <x-announcements placement="top"/>

        @include('partials.verify-banner')

        <main id="obsah" tabindex="-1" class="@yield('main-class')">
            @include('layouts.errors')

            @yield('content')
            <notification message="{{ session('flash') }}"></notification>
        </main>

        <x-announcements placement="footer"/>

        @include('layouts.footer')
    </div>

    @include('partials.ar-ready')

    {{-- Tento layout používa @yield('script'), ostatné dva @stack('scripts').
         Vyhodnocujeme oboje, aby sa pohľad nemusel starať, pod ktorým beží. --}}
    @yield('script')
    @stack('scripts')
</body>
</html>

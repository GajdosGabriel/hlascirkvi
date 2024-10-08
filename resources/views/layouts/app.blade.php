<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @yield('title')
    @yield('othermeta')

            {{--Editor--}}
    @yield('script-header')


    <!-- Fonts -->
    <link href='https://fonts.googleapis.com/css?family=Roboto:300,400,600' rel='stylesheet' type='text/css'>
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">

    <link
        rel="stylesheet"
        href="https://use.fontawesome.com/releases/v5.3.1/css/all.css"
        integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">

    {{-- https://github.com/aFarkas/lazysizes--}}
    <script src="{{ asset('js/lazysizes.min.js') }}" async=""></script>
    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    @yield('headerCSS')




    <script>
        window.App = {!! json_encode([
            'csrfToken' => csrf_token(),
                'user' => Auth::user(),
            'signedIn' => Auth::check(),
             'baseUrl' => asset('/')
        ]) !!};
    </script>



</head>
<body>

    @can('admin')
    {{-- Admin nesleduje smartlook a google statistick --}}
    @else
        @include('partials.analyticstracking')
    @endcan


    <div id="app">
        <x-navigation.main-menu/>

        <main>
            @include('layouts.errors')
            
            @yield('content')
            <notification message="{{ session('flash') }}"></notification>
        </main>

        @include('layouts.footer')
    </div>

    <script src="{{ mix('js/app.js') }}"></script>
    @yield('script')
</body>
</html>

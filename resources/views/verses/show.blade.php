@extends('layouts.app')

@section('content')
<div class="container">


    {{--<video-item></video-item>--}}

    {{--<youtube-dash></youtube-dash>--}}

    {{--<example-component></example-component>--}}

    <div class="page">
        <div class="page-content">

            <div class="page-title">
                <h1>{{ $post->title }}</h1>
                <div> Zamyslenie na {{ Carbon\Carbon::now()->format('d M Y') }}</div>
            </div>

            <div class="two-collums"> {!! $post->zamyslenie !!}<p>{{ $post->autor }}</p></div>

            <div class="nav-button">
                @if($previous)
                    <a class="btn" href="{{ URL::to( 'zamyslenia/' . $previous ) }}"><< Včera</a>
                @endif

                @if($next)
                    <a class="btn" href="{{ URL::to( 'zamyslenia/' . $next ) }}">Zajtra >></a>
                @endif
            </div>
        </div>


        <div class="page-aside">
            <div class="card">
                <div class="card-header">Verš starej zmluvy</div>

                <div class="card-body">
                    <blockquote>
                        {{ $post->biblicky_vers }}
                        <div class="footer">{{ $post->biblicky_vers_ref }}</div>
                    </blockquote>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Verš novej zmluvy</div>

                <div class="card-body">
                    <blockquote>
                        {{ $post->szvers_text }}
                        <div class="footer">{{ $post->szvers_ref }}</div>
                    </blockquote>
                </div>
            </div>

            <img src="{{ asset('images/biblia1.jpg' ) }}">
        </div>

    </div>

</div>
@endsection

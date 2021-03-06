@extends('layouts.app')

@section('content')

    <div class="container">

        <div class="page">

            <div class="page-content">

                <div>
                    <a class="tag" href="{{ route('organization.profile', [auth()->id(), auth()->user()->slug]) }}">Späť</a>
                </div>

                <div class="level">
                    <h3>Štatistika návštev - unikátne view</h3>
                    <div>
                        <a href="{{ route('admin.statistic', ['days' => 1]) }}">Dnes</a>
                        <a href="{{ route('admin.statistic', ['days' => 2]) }}">Včera</a>
                        <a href="{{ route('admin.statistic', ['days' => 7]) }}">Týždeň</a>
                    </div>

                </div>


                <table>
                    <thead>
                    <tr>
                        <th style="width: 7%">Id</th>
                        <th>Názov článku</th>
                        <th>Akt./All</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($posts as $post)
                        <tr>
                            <td>{{ $post->id }}</td>
                            <td><a href="{{ route('post.show', [$post->id, $post->id]) }}">{{ $post->title }}</a></td>
{{--                            <td>{{ $post->views_count }} / {{ $post->count_view }}</td>--}}
                            <td>{{ $post->unique_view }} / {{ $post->count_view }}</td>
                        </tr>
                    @empty
                        <table><thead><tr>Bez záznamu</tr></thead></table>
                    @endforelse
                    </tbody>
                </table>


            </div>

            <div class="page-aside">


            </div>


        </div>

    </div>


    @endsection
@extends('layouts.app')

@section('content')
    <x-pages.admin>

        <x-slot name="title">
            Štatistika návštev - unikátne view
        </x-slot>

        <x-slot name="title_right">

        </x-slot>


        <x-slot name="page">
            <div class="flex space-x-3 ">
                <a class="text-red" href="{{ route('admin.statistic', ['days' => 1]) }}">Dnes</a>
                <a href="{{ route('admin.statistic', ['days' => 2]) }}">Včera</a>
                <a href="{{ route('admin.statistic', ['days' => 7]) }}">Týždeň</a>
            </div>




            <table class="table-auto border-2 border-gray-400 rounded-md w-full">
                <thead class="bg-gray-500 text-white">
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
                            {{-- <td>{{ $post->views_count }} / {{ $post->count_view }}</td> --}}
                            <td>{{ $post->unique_view }} / {{ $post->count_view }}</td>
                        </tr>
                    @empty
                        <table>
                            <thead>
                                <tr>Bez záznamu</tr>
                            </thead>
                        </table>
                    @endforelse
                </tbody>
            </table>

        </x-slot>

    </x-pages.admin>

@endsection

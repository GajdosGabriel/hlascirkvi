@extends('layouts.app')

@section('content')
    <x-pages.admin>

        <x-slot name="title">
            Štatistika návštev - unikátne view
        </x-slot>

        <x-slot name="title_right">

        </x-slot>


        <x-slot name="page">
            <div class="flex space-x-3 mb-2">
                <x-filters.filterButton title="Dnes" type="lastDays" url="?lastDays=1" />
                <x-filters.filterButton title="Včera" type="lastDays" url="?lastDays=2" />
                <x-filters.filterButton title="Týždeň" type="lastDays" url="?lastDays=7" />
                <x-filters.filterButton title="2 týždne" type="lastDays" url="?lastDays=14" />
            </div>




            <table class="table-auto border-2 border-gray-400 rounded-md w-full">
                <thead class="bg-gray-500 text-white">
                    <tr>
                        <th style="width: 7%">Id</th>
                        <th>Názov článku</th>
                        <th>Organizácia</th>
                        <th>Akt./All</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($posts as $post)
                        <tr class="border-2 border-gray-300  hover:bg-gray-100">
                            <td>{{ $post->id }}</td>
                            <td>
                                <a href="{{ route('post.show', [$post->id, $post->slug]) }}">
                                    {{ Str::limit($post->title, 45) }}
                                </a>
                            </td>
                            <td>{{ $post->organization }}</td>
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

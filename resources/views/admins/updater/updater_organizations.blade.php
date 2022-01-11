@extends('layouts.app')

@section('content')


    <div class="grid grid-cols-12 gap-6  ">



        @include('admins._profil-menu')



        <div class="col-span-5">

            <x-pages.page_title>
                <x-slot name="title">

                    Kanály pre {{ $updater->title }}

                </x-slot>


            </x-pages.page_title>


            @forelse ( $updater->organizations as $tag )

                <form action="{{ route('updater.organization.destroy', [$updater->id, $tag->id]) }}" method="POST"
                    class="flex justify-between mb-4 border-b-2 hover:bg-gray-50 border-dashed">

                    @csrf @method('DELETE')
                    <div class="font-semibold text-lg">{{ $tag->title }}</div>


                    <button class="cursor-pointer ">X</button>


                    {{-- @can('update', $tag)
                        <tag-dropdown :post="{{ $tag }}" />
                    @endcan --}}
                </form>

            @empty
                žiadne kanály

            @endforelse



        </div>

        <div class="col-span-3">

            <h2 class="page_title">Pridať kanál</h2>

            @include('tags.form')


        </div>

    </div>



@endsection

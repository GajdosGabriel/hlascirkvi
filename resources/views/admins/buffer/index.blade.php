@extends('layouts.app')

@section('title')
    <title>{{ 'Admin nezverejnevé videa' }}</title>
@endsection

@section('content')
    <x-pages.dashboard>


        <x-slot name="title">

            Buffer príspevky (Nezverenené)

        </x-slot>

        <x-slot name="title_right">


        </x-slot>


        <x-slot name="page">

            <div class="grid md:grid-cols-3 lg:grid-cols-5 md:gap-7 grid-cols-2 gap-2">


                @forelse($posts as $post)
                    {{-- <card-front :post="{{ $post }}"></card-front> --}}
                    @include('posts.card-front')
                @empty
                    bez záznamu
                @endforelse

            </div>

            <div class="md:block flex justify-center my-8">
                {{ $posts->links() }}
            </div>



            <div class="grid col-span-2">
                @include('admins.buffer.list-organizations')
            </div>

        </x-slot>
        </x-pages.admin>
    @endsection

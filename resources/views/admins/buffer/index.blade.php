@extends('layouts.admin')

@section('title')
    <title>{{ 'Admin nezverejnevé videa' }}</title>
@endsection

@section('content')
    <x-pages.admin>


        <x-slot name="title">

            Buffer príspevky (Nezverenené)

        </x-slot>

        <x-slot name="title_right">


        </x-slot>


        <x-slot name="page">

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">


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
                @include('admins.buffer.plan')
                @include('admins.buffer.list-organizations')
            </div>

        </x-slot>
        </x-pages.admin>
    @endsection

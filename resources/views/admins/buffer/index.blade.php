@extends('layouts.app')

@section('content')

    <x-pages.admin>


        <x-slot name="title">

            Buffer príspevky (Nezverenené)

        </x-slot>

        <x-slot name="title_right">


        </x-slot>


        <x-slot name="page">

            <div class="grid md:grid-cols-3 lg:grid-cols-5 md:gap-7 grid-cols-2 gap-2">


                @forelse($posts as $post)
                    {{-- <post-card :post="{{ $post }}"></post-card> --}}
                    @include('posts.post-card')
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

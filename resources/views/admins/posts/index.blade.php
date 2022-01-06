@extends('layouts.app')

@section('content')

@component('components.pages.admin')


        @slot('title')

            Všetky príspevky

        @endslot

        @slot('title_right')

        @endslot


        @slot('page')

            <div class="grid md:grid-cols-3 lg:grid-cols-4 md:gap-7 grid-cols-2 gap-2">


                @forelse($posts as $post)

                    <post-card :post="{{ $post }}"></post-card>
                    {{-- @include('posts.post-card') --}}
                @empty
                    bez záznamu
                @endforelse

            </div>

            <div class="md:block flex justify-center my-8">
                {{ $posts->links() }}
            </div>



            <div class="grid col-span-2">
               
            </div>

        @endslot
    @endcomponent





@endsection

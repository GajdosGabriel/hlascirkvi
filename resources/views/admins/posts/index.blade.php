@extends('layouts.app')

@section('title')
    <title>{{ "Admin články" }}</title>
@endsection

@section('content')

    <x-pages.admin>


        <x-slot name="title">

            Všetky príspevky

        </x-slot>

        <x-slot name="title_right">
          
        </x-slot>


        <x-slot name="page">

            <div class="">

                @forelse($posts as $post)
                    @include('posts.post-card-admin')
                @empty
                    bez záznamu
                @endforelse

            </div>

            <div class="md:block flex justify-center my-8">
                {{ $posts->links() }}
            </div>



            <div class="grid col-span-2">

            </div>

        </x-slot>
    </x-pages.admin>

@endsection

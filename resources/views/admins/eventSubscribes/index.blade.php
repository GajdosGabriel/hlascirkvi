@extends('layouts.app')

@section('title')
    <title>{{ "Admin Prihlásený" }}</title>
@endsection

@section('content')

    <x-pages.admin>


        <x-slot name="title">

            Všetky prihlásenia

        </x-slot>

        <x-slot name="title_right">
          
        </x-slot>


        <x-slot name="page">

            <div class="">

                @forelse($items as $item)
              <div class="font-semibold">{{ $item->created_at }}</div> <br/>
              <div class="font-semibold">{{ $item->event->title }}</div> <br/>
                {{ $item->organization->title }} <br/>
                    {{-- @include('posts.card-admin') --}}
                @empty
                    bez záznamu
                @endforelse

            </div>

            <div class="md:block flex justify-center my-8">
                {{ $items->links() }}
            </div>



            <div class="grid col-span-2">

            </div>

        </x-slot>
    </x-pages.admin>

@endsection

@extends('layouts.app')

@section('content')
<div class="grid grid-cols-12 gap-6  ">

    <div class="grid col-span-2  min-h-screen">
        <div class="flex flex-col bg-gray-200">

            @include('admins._profil-menu')

        </div>
    </div>


    <div class="grid col-span-10">

                <h3 class="page_title text-2xl">Buffer príspevky (Nezverenené)</h3>

                <div class="grid md:grid-cols-3 lg:grid-cols-4 md:gap-7 grid-cols-2 gap-2">
                    @forelse($posts as $post)

                        <post-card :post="{{ $post }}"></post-card>
                        {{--                        @include('posts.post-card')--}}
                    @empty
                        bez záznamu
                    @endforelse
                </div>

                {{ $posts->links() }}
            </div>

            <div class="md:w-3/12">
                @include('admins.buffer.list-organizations')
            </div>


        </div>

    </div>


@endsection

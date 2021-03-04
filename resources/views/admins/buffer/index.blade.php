@extends('layouts.app')

@section('content')

    <div class="page">
        <div class="md:flex">
            {{--  Stlpec I. --}}
            <div class="md:w-8/12 m-6">
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

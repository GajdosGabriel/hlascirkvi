@extends('layouts.app')

@section('content')

@component('layouts.pages.admin')
@slot('page')

    <div class="col-span-8">

        <div class="page_title flex justify-between">
            <h2 class="text-2xl">Buffer príspevky (Nezverenené)</h2>
        </div>

        <div class="grid md:grid-cols-3 lg:grid-cols-4 md:gap-7 grid-cols-2 gap-2">
            @forelse($posts as $post)

                <post-card :post="{{ $post }}"></post-card>
                {{--   @include('posts.post-card')--}}
            @empty
                bez záznamu
            @endforelse
        </div>

        <div class="md:block flex justify-center my-8">
            {{ $posts->links() }}
        </div>
    </div>


    <div class="grid col-span-2">
        @include('admins.buffer.list-organizations')
    </div>

@endslot
@endcomponent





@endsection

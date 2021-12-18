@extends('layouts.app')
@section('title') <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title> @endsection

@section('content')

    @component('layouts.components.pages.profil')
        @slot('page')



            @component('layouts.components.pages.page_title')
                @slot('title')
                    Články
                @endslot

                @slot('title_right')
                    <a href="{{ route('organization.post.create', $organization->id) }}" class="btn btn-primary">Nový článok</a>
                @endslot
            @endcomponent


            <div class="">
                @forelse($posts as $post)

                    @include('posts._post-list')
                @empty
                    bez záznamu
                @endforelse

            </div>

            <div class="md:block flex justify-center my-8">
                {{ $posts->links() }}
            </div>


        @endslot
    @endcomponent

@endsection

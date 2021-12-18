@extends('layouts.app')

@section('content')
    @component('layouts.components.pages.profil')
        @slot('page')

            <div class="col-span-8">
                @include('layouts.errors')

                @component('layouts.components.pages.page_title')
                    @slot('title')

                        Upraviť článok

                    @endslot

                    @slot('title_right')

                    @endslot
                @endcomponent


                <form method="POST" action="{{ route('organization.post.update', [$post->organization_id, $post->id]) }}"
                    enctype="multipart/form-data">
                    @csrf @method('PUT')
                    @include('posts.form')
                </form>
            </div>

            <div class="md:w-4/12">
                {{-- aside --}}
            </div>
            </div>

            @include('posts.editor')
            </div>
        @endslot
    @endcomponent

@endsection

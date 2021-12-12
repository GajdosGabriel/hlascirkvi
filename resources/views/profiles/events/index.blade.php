@extends('layouts.app')
@section('title') <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title> @endsection

@section('content')

    @component('layouts.pages.profil')
        @slot('page')
            <div class="col-span-8">

                @component('layouts.pages.page_title')
                    @slot('title')

                        {{ $title ?? 'Pozvánky na podujatia' }}

                    @endslot

                    @slot('title_right')
                        <a class="btn btn-default"
                            href="{{ route('akcie.create') }}">
                            Nové podujatie
                        </a>
                    @endslot
                    
                @endcomponent
                @slot('title_right')
                <a href="{{ route('posts.create') }}" class="btn btn-primary">Nový článok</a>
            @endslot

                {{-- Upcoming events --}}
                @forelse($events as $event)
                    @include('events._list_items')
                @empty
                    bez podujatí
                @endforelse

                <div class="md:block flex justify-center my-8">
                    {{ $events->links() }}
                </div>
            </div>
        @endslot
    @endcomponent

@endsection

@extends('layouts.app')
@section('title') <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title> @endsection

@section('content')

    @component('layouts.components.pages.profil')
        @slot('page')
            <div class="col-span-8">

                @component('layouts.components.pages.page_title')
                    @slot('title')

                        {{ $title ?? 'Pozvánky na podujatia' }}

                    @endslot

                    @slot('title_right')
                        <a class="btn btn-default"
                            href="{{ route('organization.event.create', $organization->id) }}">
                            Nové podujatie
                        </a>
                    @endslot
                    
                @endcomponent
                @slot('title_right')
                <a href="{{ route('posts.create') }}" class="btn btn-primary">Nový článok</a>
            @endslot

                {{-- Upcoming events --}}
                @forelse($events as $event)
                    @include('prayer.index')
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

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

                    @slot('title_site')

                        <a class="border-2 border-blue-400 p-1 px-2 rounded-md shadow-sm hover:bg-blue-300"
                            href="{{ route('akcie.create') }}"><i class="fas fa-plus"></i>
                            Nové podujatie
                        </a>

                    @endslot
                @endcomponent


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

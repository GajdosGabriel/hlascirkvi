@extends('layouts.admin')
@section('title')
    <title>{{ 'Admin modlitby' }}</title>
@endsection

@section('content')
    <x-pages.admin>

        <x-slot name="title">
            Modlitby
        </x-slot>

        <x-slot name="title_right">
            {{-- Modlitba sa zakladá do kanála, takže bez prideleného kanála
                 nie je kam odkázať — route() by na prázdnom {canal} spadla. --}}
            @if (auth()->user()->canal_id)
                <a class="ar-btn ar-btn--accent" href="{{ route('profile.canals.prayers.create', auth()->user()->canal_id) }}">
                    Nová modlitba
                </a>
            @endif
        </x-slot>

        <x-slot name="page">
            <ul>
                @foreach ($prayers as $prayer)
                    <li class="ar-panel mb-3 p-4">
                        <div class="flex flex-wrap justify-between gap-2">
                            <div>{{ $prayer->title }}</div>
                            <div class="flex space-x-1 items-center">
                                <dropdown-slot label="Spravovať modlitbu">
                                    <a href="{{ route('profile.canals.prayers.edit', [$prayer->canal_id, $prayer->id]) }}">
                                        <i class="fas fa-pen" aria-hidden="true"></i> Upraviť
                                    </a>

                                    <hr class="ui-dropdown__divider">

                                    <form
                                        action="{{ route('profile.canals.prayers.destroy', [$prayer->canal_id, $prayer->id]) }}"
                                        method="post">
                                        @method('DELETE') @csrf
                                        <button type="submit" class="ui-dropdown__item--danger">
                                            <i class="far fa-trash-alt" aria-hidden="true"></i> Zmazať
                                        </button>
                                    </form>
                                </dropdown-slot>
                            </div>

                        </div>
                        <div>{{ $prayer->body }}</div>

                        <div class="flex">
                            <div class="text-gray-400 text-sm font-semibold mr-4">Meno: {{ $prayer->user_name ?: '—' }}</div>
                            <div class="text-gray-400 text-sm">Vytvorené: {{ $prayer->created_at->format('m. d. Y') }}
                            </div>
                        </div>

                    </li>
                @endforeach
            </ul>

            <div class="md:block my-8">
                {{ $prayers->onEachSide(1)->links() }}
            </div>
        </x-slot>
        </x-pages.admin>
    @endsection

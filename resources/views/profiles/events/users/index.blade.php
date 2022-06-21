@extends('layouts.app')
@section('title') <title>{{ 'Vzdelávanie, konferencie a púte.' }}</title> @endsection

@section('content')


    {{-- @section('title', $title) --}}

    <x-pages.admin>

        <x-slot name="title">
            Prihlášky na: {{ $event->title }}
        </x-slot>


        <x-slot name="title_right">

        </x-slot>

        <x-slot name="page">
            <table class="table-auto border-2 border-gray-400 rounded-md w-full">
                <thead class="bg-gray-500 text-white">
                    <tr>
                        <th>P.č.</th>
                        <th>Dňa</th>
                        <th>Meno</th>
                        <th>GDPR</th>
                        <th>Prihlášku</th>
                        <th>Vstupenka</th>
                        <th>Zmazať</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($event->subscribes as $subcription)
                        <tr class="border-2 border-gray-300">
                            <td class="px-2 text-center">{{ $loop->iteration }}.</td>
                            <td class="text-center">{{ date('d M Y', strtotime($subcription->created_at)) }}</td>
                            <td class="text-center">
                                {{ $subcription->organization->title }}
                            </td>
                            <td class="text-center">
                                <a target="_blank"
                                    href="{{ route('event.gdpr', [$event->id, $subcription->organization->id, $subcription->organization->slug]) }}">
                                    <i title="Súhlas k GDPR" class="far fa-file-pdf"></i>
                                </a>
                            </td>
                            <td class="text-center">
                                <form method="post"
                                    action="{{ route('event.subscribe.update', [$event->id, $subcription->id]) }}">
                                    @csrf @method('PUT')
                                    @if ($subcription->confirmed)
                                        <input name="confirmed" value="{{ null }}" type="hidden" />
                                        <button title="Zrušiť potvrdenie!" class="px-2">Potvrdené</button>
                                    @else
                                        <input name="confirmed" value="{{ now() }}" type="hidden" />
                                        <button class="px-2">Potvrdiť prihlášku</button>
                                    @endif
                                </form>
                            </td>

                            <td class="text-center">
                                <span>Generovaná</span>
                            </td>

                            <td class="text-center">
                                <form method="post"
                                    action="{{ route('event.subscribe.destroy', [$event->id, $subcription->id]) }}">
                                    @csrf @method('DELETE')

                                    <button class="px-2">Zmazať</button>
                                </form>
                            </td>

                        </tr>
                    @empty
                        <table>
                            <tbody>
                                <tr>
                                    žiadny prihlásený
                                </tr>
                            </tbody>
                        </table>
                    @endforelse
                </tbody>

            </table>
        </x-slot>
    </x-pages.admin>
@endsection

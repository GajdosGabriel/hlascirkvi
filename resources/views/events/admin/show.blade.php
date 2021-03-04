@extends('layouts.app')


{{--@section('title', $title)--}}


@section('content')

    <div class="container">

        <div class="page">

            <div class="event-content">

                <div class="page-header flex">
                    <h3>Prihlásený na: {{ $event->title }}</h3>
                </div>



                <table>
                    <thead>
                    <tr>
                        <th>P.č.</th>
                        <th>Dňa</th>
                        <th>Meno</th>
                        <th>GDPR</th>
                        <th>Prihlášku</th>
                        <th>Vstupenka</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($event->eventSubscribe as $subcription)
                        <tr>
                            <td>{{ $loop->iteration }}.</td>
                            <td>{{ date('d M Y', strtotime($subcription->created_at)) }}</td>
                            <td>
                                {{ $subcription->organization->title }}
                            </td>
                            <td>
                                <a target="_blank" href="{{ route('event.gdpr', [$event->id, $subcription->organization->id, $subcription->organization->slug]) }}">
                                    <i title="Súhlas k GDPR" class="far fa-file-pdf"></i>
                                </a>
                            </td>
                            <td>
                                @if($subcription->confirmed)
                                    <a title="Zrušiť potvrdenie!">Potvrdené</a>
                                @else
                                    <a href="{{ route('event.disabled', [$subcription->id]) }}" class="btn btn-small">Prijať</a>
                                @endif
                            </td>

                            <td>
                                <a href="#">Generovaná</a>
                            </td>

                        </tr>
                    @empty
                        žiadny registrovaný
                    @endforelse
                    </tbody>

                </table>
            </div>
        </div>
    </div>


@endsection

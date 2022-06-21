<div class="flex justify-between items-center">
    @if (!$event->displayStatus())
        <div><label class="badge badge-default " title="Podujatie sa skončilo">
                {{ trans('web.events_users_finished') }}</label>
        </div>
    @elseif($event->published)
        <div title="Kliknutím pozastavíte publikovanie akcie.">
            <label class="badge badge-info"
                title="Pozastaviť zobrazovanie?">{{ trans('web.events_users_is_active') }}</label>
        </div>
    @else
        <div title="Spustíť publikovanie akcie.">{{ $event->count_view }} <label class="badge badge-danger"
                style="cursor: pointer">{{ trans('web.events_users_no_active') }}</label>
        </div>
    @endif

    <i title="Počet zobrazení" class="fa fa-eye mt-1"> {{ $event->count_view }} </i>

    <div>
        <a href="{{ route('event.subscribe.index', [$event->id]) }}">Prihlásených:
            {{ $event->subscribes()->count() }}</a>
    </div>

    @if (!$event->published)
        <span class="px-1 text-xs bg-red-500 text-gray-100 rounded border-2 border-red-700">Nepublikované</span>
    @endif

</div>

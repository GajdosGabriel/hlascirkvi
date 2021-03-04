{{--<form-organization></form-organization>--}}


{{--<div class="form-group">--}}
    {{--<label for="">{{ trans('web.events_dateStart') }}</label>--}}
    {{--<input type="date" name="dateStart" value="{{ old('dateStart') ?? $event->dateStart->format('Y-m-d') }}" class="form-control" required>--}}
{{--</div>--}}

{{--<div class="form-group">--}}
{{--    <label for="dateStart">{{ trans('web.events_dateStart') }}</label>--}}
{{--    <input type="datetime-local" name="start_at" value="{{ old('start_at') ?? $event->start_at->format('Y-m-d\TH:i') }}" id="dateStart" class="form-control" required>--}}
{{--</div>--}}

<div class="form-group">
    <label for="dateStart">{{ trans('web.events_dateStart') }}</label>
    <input type="datetime-local" name="start_at" value="{{ old('start_at') ?? $event->start_at->format('Y-m-d\TH:i') }}" id="dateStart" class="form-control" required>
</div>

<div class="form-group">
    <label for="dateend">{{ trans('web.events_dateEnd') }}</label>
    <input type="datetime-local" name="end_at" value="{{ old('end_at') ?? $event->end_at->format('Y-m-d\TH:i') }}" id="dateend" class="form-control" required>
</div>


{{--<div class="form-group">--}}
    {{--<label for="">{{ trans('web.events_timeStart') }}</label>--}}
    {{--<input type="time" name="timeStart" class="form-control input-sm" value="{{ old('timeStart') ?? $event->dateStart->format('H:i') }}" required>--}}
    {{--<input type="time" name="timeStart" class="form-control input-sm" value="{{ old('timeStart') ?? $event->timeStart->format('H:i') }}" required>--}}
{{--</div>--}}


{{--<get-organization :organizations="{{ auth()->user()->organizations }}"></get-organization>--}}

<div class="form-group">
    <label>{{ trans('web.events_organizator') }}</label>
    <select class="form-control" name="organization_id" required>
        <option disabled value="" selected hidden>---Vybrať---</option>
        @foreach(auth()->user()->organizations as $organization)
            <option
                    @if( $event->organization_id == $organization->id OR $organization->id == Auth::user()->owner->id )
                    selected
                    @endif
                    value="{{ $organization->id }}">{{ $organization->title }}
            </option>
        @endforeach
    </select>
</div>


{{--<div class="form-group">--}}
    {{--<label for="">{{ trans('web.events_street') }}</label>--}}
    {{--<input type="text" name="street" value="{{ old('street') ?? $event->street }}"  placeholder="Adresa konania" class="form-control input-sm" >--}}
{{--</div>--}}

<div class="form-group">
    <label for="">{{ trans('web.events_region_of_event') }}</label>
    <select name="village_id" class="form-control input-sm" required>
        <option label="{{ trans('web.select') }}"></option>

        @foreach(\App\Village::all() as $village)
        <option @if($event->village_id == $village->id)  selected @endif value="{{ $village->id }}">{{ $village->fullname }}  {{ $village->zip }}</option>
        @endforeach
    </select>
</div>


<div class="form-group">
    <label for="">{{ trans('web.events_clientwww') }}</label>
    <input type="text" name="clientwww" value="{{ old('clientwww') ?? $event->clientwww }}" placeholder="Odkaz na váš webovú stánku" class="form-control input-sm">
</div>

<div class="form-group">
    <label for="">{{ trans('web.events_registration') }}</label>
    <select name="registration" class="form-control input-sm" required>
        {{--<option label="{{ trans('web.select') }}"></option>--}}
        <option @if($event->registration == 'no')  selected @endif  value="no">Bez rezervácie</option>
        <option @if($event->registration == 'recomended')  selected @endif value="recomended">Doporučuje sa</option>
        <option @if($event->registration == 'yes')  selected @endif  value="yes">Požaduje sa registrácia</option>
    </select>
</div>

<div class="form-group">
    <label for="">{{ trans('web.events_entryFee') }}</label>
    <select name="entryFee" class="form-control input-sm" required>
        {{--<option label="{{ trans('web.select') }}"></option>--}}
        <option @if($event->entryFee == 'no')  selected @endif value="no">Bez vstupného</option>
        <option @if($event->entryFee == 'voluntarily')  selected @endif value="voluntarily">Dobrovoľné</option>
        <option @if($event->entryFee == 'yes')  selected @endif value="yes">Povinné</option>
    </select>
</div>

<div class="flex">
    <div class="inline">
        <label>{{ trans('web.events_published_now') }}</label>
        <input type="radio" value="1"
               @if( isset($event->published) AND $event->published == 1)
               checked @else checked @endif
               name="published">
    </div>

    <div class="inline">
        <label>{{ trans('web.events_published_later') }}</label>
        <input type="radio" value="0"
               @if( isset($event->published) AND $event->published == 0)
               checked
               @endif
               name="published">
    </div>
</div>

<div class="form-group my-5">
    <button type="submit" class="btn w-full"> {{ $buttonText ?? trans('web.btn_save') }}</button>
</div>

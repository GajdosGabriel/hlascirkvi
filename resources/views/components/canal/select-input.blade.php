<div class="form-group">
    <label>{{ trans('web.organization_select') }}</label>
    <select class="form-control" name="organization_id" required>
        <option disabled value="" selected hidden>---Vybrať---</option>
        @foreach(auth()->user()->organizations as $organization)
            <option
                    {{-- @if( $event->organization_id == $organization->id OR $organization->id == Auth::user()->org_id )
                    selected
                    @endif --}}
                    value="{{ $organization->id }}">{{ $organization->title }}
            </option>
        @endforeach
    </select>
</div>
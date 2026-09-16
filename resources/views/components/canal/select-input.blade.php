<div class="form-group">
    <label>{{ trans('web.organization_select') }}</label>
    <select class="form-control" name="canal_id" required>
        <option disabled value="" selected hidden>---Vybrať---</option>
        @foreach(auth()->user()->canals as $canal)
            <option
                    {{-- @if( $event->canal_id == $canal->id OR $canal->id == Auth::user()->canal_id )
                    selected
                    @endif --}}
                    value="{{ $canal->id }}">{{ $canal->title }}
            </option>
        @endforeach
    </select>
</div>
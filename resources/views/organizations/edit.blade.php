@extends('layouts.app')

@section('content')
    @component('components.pages.profil')

        @include('layouts.errors')

        @slot('title')

            Upraviť kanál

        @endslot

        @slot('title_right')

        @endslot



        @slot('page')

            <form method="post" action="{{ route('user.organization.update', [$user->id, $organization->id]) }}" >
                @csrf @method('PUT')
                <div>

                    <div class="form-group">
                        <label for="title">Názov</label>
                        <input type="text" name="title" id="title" value="{{ $organization->title }}" class="form-control"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="street">Ulica</label>
                        <input type="text" name="street" id="street" value="{{ $organization->street }}" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="">Mesto</label>
                        <select name="village_id" class="form-control input-sm">
                            <option label="{{ trans('web.select') }}"></option>

                            @foreach (\App\Models\Village::all() as $village)
                                <option @if ($organization->village_id == $village->id) selected @endif value="{{ $village->id }}">
                                    {{ $village->fullname }} {{ $village->zip }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Web stránka</label>
                        <input type="text" name="url_www" class="form-control" placeholder="web stránka">
                    </div>


                    @foreach ($organization->users as $user)
                        <div class="block">
                            admin: {{ $user->last_name }}
                        </div>
                    @endforeach


                    <span style="font-weight: 600">Organizácia patrí</span><br>
                    @forelse(\App\Models\Updater::all() as $updater)
                        @if ($updater->type == 'denomination')
                            <input required type="radio" name="updaters[]" value="{{ $updater->id }}" @foreach ($organization['updaters'] as $up)
                            @if ($up->pivot->updater_id == $updater->id)
                                checked @endif
                            Žiadne položky
                        @endforeach
                        >
                        {{ $updater->title }}<br>
                    @endif
                @empty
                    žiadny tag
                    @endforelse



                    @if (auth()->user()->hasRole('admin'))

                        <div class="form-group">
                            <label class="font-semibold" for="youtube_channel">youtube channel</label>
                            <input type="text" name="youtube_channel" class="form-control" placeholder="youtube channel"
                                id="youtube_channel" value="{{ old('youtube_channel') ?? $organization->youtube_channel }}">
                        </div>

                        <div class="form-group">
                            <label class="font-semibold" for="youtube_playlist">youtube playlist</label>
                            <input type="text" name="youtube_playlist" class="form-control" placeholder="youtube playlist"
                                id="youtube_playlist" value="{{ old('	youtube_playlist') ?? $organization->youtube_playlist }}">
                        </div>

                        <div style="font-weight: 600">Article modifíkácia
                            <input type="text" name="mod_title" value="{{ old('mod_title') ?? $organization->mod_title }}"
                                placeholder="Meno pred názvom článku">
                        </div>


                        <span style="font-weight: 600">Organizácia na predný zoznam</span><br>
                        @forelse(\App\Models\Updater::all() as $updater)
                            @if ($updater->type == 'frontUser')
                                <input type="checkbox" name="updaters[]" value="{{ $updater->id }}" @foreach ($organization['updaters'] as $up)
                                @if ($up->pivot->updater_id == $updater->id)
                                    checked @endif
                                Žiadne položky
                            @endforeach
                            >
                            {{ $updater->title }}<br>
                        @endif
                    @empty
                        žiadny tag
                    @endforelse

                    <span style="font-weight: 600">Zaradená do zoznamu</span><br>
                    @forelse(\App\Models\Updater::all() as $updater)
                        @if ($updater->type == 'list')
                            <input type="checkbox" name="updaters[]" value="{{ $updater->id }}" @foreach ($organization['updaters'] as $up)
                            @if ($up->pivot->updater_id == $updater->id)
                                checked @endif
                            Žiadne položky
                        @endforeach
                        >
                        {{ $updater->title }}<br>
                    @endif
                @empty
                    žiadny tag
                    @endforelse


                    <span style="font-weight: 600">Vyhľadávanie podľa mena v týždni</span><br>
                    @forelse(\App\Models\Updater::all() as $updater)
                        @if ($updater->type == 'dayOfWeek')
                            <input type="checkbox" name="updaters[]" value="{{ $updater->id }}" @foreach ($organization['updaters'] as $up)
                            @if ($up->pivot->updater_id == $updater->id)
                                checked @endif
                            Žiadne položky
                        @endforeach
                        >
                        {{ $updater->title }}<br>
                    @endif
                @empty
                    žiadny tag
                    @endforelse
                    @endif

                    <div class="text-right">
                        <button type="submit" class="btn btn-primary">Uložiť</button>
                    </div>

                </div>
            </form>

        @endslot
    @endcomponent

@endsection

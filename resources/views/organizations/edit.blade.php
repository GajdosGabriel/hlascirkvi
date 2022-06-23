@extends('layouts.app')

@section('content')
    <x-pages.admin>

        @include('layouts.errors')

        <x-slot name="title">

            Upraviť kanál

        </x-slot>


        <x-slot name="title_right">

        </x-slot>



        <x-slot name="page">

            <form method="post" action="{{ route('user.organization.update', [$user->id, $organization->id]) }}">
                @csrf @method('PUT')
                <div>

                    <div class="form-group">
                        <label for="title">Názov</label>
                        <input type="text" name="title" id="title" value="{{ $organization->title }}"
                            class="form-control" required>
                    </div>

                    {{-- Text Field --}}
                    <div class="form-group {{ $errors->has('description') ? ' has-error' : '' }}">
                        <label for="street">Popis kanálu</label>
                        <textarea class="form-control" rows="3" name="description" placeholder="Popis kanálu ...">{{ old('description') ?? $organization->description }}</textarea>
                        @if ($errors->has('description'))
                            <span class="invalid-feedback">
                                <strong>{{ $errors->first('description') }}</strong></span>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="street">Ulica</label>
                        <input type="text" name="street" id="street" value="{{ $organization->street }}"
                            class="form-control">
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

                    <div class="form-group">
                        <label for="email">Kontaktný email kanálu</label>
                        <input type="email" name="email" id="email" value="{{ $organization->email }}"
                            class="form-control" placeholder="email na komunikáciu">
                    </div>

                    <div class="form-group">
                        <label class="font-semibold" for="">Admin kanálu</label>
                        @foreach ($organization->users as $user)
                            <div class="block">
                                <div class="btn-small btn-primary whitespace-nowrap w-min">{{ $user->fullname }}</div>
                            </div>
                        @endforeach
                    </div>

                    @can('superadmin')
                        <div class="bg-blue-200 mb-4 p-3">
                            <div class="font-semibold">Super Admin area</div>
                            <div class="form-group">
                                <select name="users[]" class="form-control input-sm" multiple required>
                                    @foreach (\App\Models\User::all() as $user)
                                        <option value="{{ $user->id }}" @if ($organization->users()->whereId($user->id)->first()) selected @endif>
                                            {{ $user->fullname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>


                            <form method="POST"
                                action="{{ route('user.organization.update', [$user->id, $organization->id]) }}">
                                @csrf @method('PUT')
                                <div class="font-semibold" for="">Kanál je:</div>
                                @if ($organization->published)
                                    <button value="0" name="published"
                                        class="px-2 bg-green-500 text-gray-100 rounded border-2 border-green-700 hover:bg-green-600">Publikovaný</button>
                                @else
                                    <button value="1" name="published"
                                        class="px-2 bg-red-500 text-gray-100 rounded border-2 border-red-700 hover:bg-red-600">Nepublikovaný</button>
                                @endif
                            </form>
                        </div>
                    @endcan

                    <div class="font-semibold">Kanál určený pre publikum:</div>
                    @forelse(\App\Models\Updater::all() as $updater)
                        @if ($updater->type == 'denomination')
                            <input required type="radio" name="updaters[]" value="{{ $updater->id }}"
                                @foreach ($organization['updaters'] as $up) @if ($up->pivot->updater_id == $updater->id)
                                checked @endif
                                Žiadne položky @endforeach
                            >
                            {{ $updater->title }}<br>
                        @endif
                        @empty
                            žiadny tag
                        @endforelse



                        @if (auth()->user()->hasRole('admin'))
                            <div class="form-group">
                                <label class="font-semibold" for="youtube_channel">Channel YT</label>
                                <input type="text" name="youtube_channel" class="form-control"
                                    placeholder="Channel organizácie na youtube" id="youtube_channel"
                                    value="{{ old('youtube_channel') ?? $organization->youtube_channel }}">
                            </div>

                            <div class="form-group">
                                <label class="font-semibold" for="youtube_playlist">Playlist YT</label>
                                <input type="text" name="youtube_playlist" class="form-control"
                                    placeholder="Playlist organizácie na youtube" id="youtube_playlist"
                                    value="{{ old('	youtube_playlist') ?? $organization->youtube_playlist }}">
                            </div>

                            <div class="mb-2 font-semibold">Article modifíkácia
                                <input type="text" name="mod_title"
                                    value="{{ old('mod_title') ?? $organization->mod_title }}"
                                    class="p-1 border-solid border-2" placeholder="Meno pred názvom článku">
                            </div>

                            <div class="mb-2 font-semibold">
                                <div>Organizácia na predný zoznam</div>
                                @forelse(\App\Models\Updater::all() as $updater)
                                    @if ($updater->type == 'frontUser')
                                        <input type="checkbox" name="updaters[]" value="{{ $updater->id }}"
                                            @foreach ($organization['updaters'] as $up) @if ($up->pivot->updater_id == $updater->id)
                                    checked @endif
                                            Žiadne položky @endforeach
                                        >
                                        {{ $updater->title }}<br>
                                    @endif
                                @empty
                                    žiadny tag
                                @endforelse
                            </div>

                            <div class="mb-2">
                                <div class="font-semibold">Videa publikovať v zozname</div>
                                @forelse(\App\Models\Updater::all() as $updater)
                                    @if ($updater->type == 'post')
                                        <input type="checkbox" name="updaters[]" value="{{ $updater->id }}"
                                            @foreach ($organization['updaters'] as $up) @if ($up->pivot->updater_id == $updater->id)
                                    checked @endif
                                            Žiadne položky @endforeach
                                        >
                                        {{ $updater->title }}<br>
                                    @endif
                                @empty
                                    žiadny tag
                                @endforelse
                            </div>

                            <div class="mb-2 font-semibold">
                                <div>Zaradená do zoznamu</div>
                                @forelse(\App\Models\Updater::all() as $updater)
                                    @if ($updater->type == 'listOfOrganization')
                                        <input type="checkbox" name="updaters[]" value="{{ $updater->id }}"
                                            @foreach ($organization['updaters'] as $up) @if ($up->pivot->updater_id == $updater->id)
                                checked @endif
                                            Žiadne položky @endforeach
                                        >
                                        {{ $updater->title }}<br>
                                    @endif
                                @empty
                                    žiadny tag
                                @endforelse
                            </div>

                            <div class="mb-2 font-semibold">
                                <div>Vyhľadávanie podľa mena v týždni</div>
                                @forelse(\App\Models\Updater::all() as $updater)
                                    @if ($updater->type == 'dayOfWeek')
                                        <input type="checkbox" name="updaters[]" value="{{ $updater->id }}"
                                            @foreach ($organization['updaters'] as $up) @if ($up->pivot->updater_id == $updater->id)
                                checked @endif
                                            Žiadne položky @endforeach
                                        >
                                        {{ $updater->title }}<br>
                                    @endif
                                @empty
                                    žiadny tag
                                @endforelse
                            </div>
                        @endif

                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">Uložiť</button>
                        </div>


                </form>

            </x-slot>
        </x-pages.admin>

    @endsection

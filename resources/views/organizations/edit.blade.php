@extends('layouts.app')

@section('content')

    <div class="page">
        @include('organizations._profil-menu')
        <div class="">

            <div class="w-6/12">



                <h3 class="font-semibold">Upraviť {{ $organization->title }}</h3>

                <form method="post" action="{{ route('organization.update', [$organization->id]) }}">
                    @csrf @method('PUT')
                    <div class="card-body" style="width: 50%">

                    <div class="form-group">
                        <label for="title">Názov</label>
                        <input type="text" name="title" id="title" value="{{ $organization->title }}" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="street">Ulica</label>
                        <input type="text" name="street" id="street" value="{{ $organization->street }}" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="city">Mesto</label>
                        <input type="text" name="city" id="city" value="{{ $organization->city }}" class="form-control">
                    </div>


                    <div class="form-group">
                        <label>Región</label>
                        <select name="region_id" required>
                            <option value="" selected disabled >Vybrať kategóriu</option>
                            @foreach(\App\Region::all() as $region)
                                <option class="option"
                                        @if( isset($organization->region_id) AND $organization->region_id == $region->id )
                                        selected
                                        @endif
                                        value="{{ $region->id }}"
                                >
                                    {{ $region->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                    @foreach($organization->users as $user)
                        <span>
                           admin: {{ $user->last_name }}
                        </span></br>
                    @endforeach


                        <span style="font-weight: 600">Organizácia patrí</span><br>
                        @forelse(\App\Updater::all() as $updater)
                            @if( $updater->type == 'denomination')
                                <input required type="radio" name="updaters[]" value="{{ $updater->id }}"
                                       @foreach($organization['updaters'] as $up)
                                       @if($up->pivot->updater_id == $updater->id )
                                       checked
                                       @endif
                                       Žiadne položky
                                       @endforeach
                                >
                                {{ $updater->title }}<br>
                            @endif
                        @empty
                            žiadny tag
                        @endforelse



                    @if( auth()->user()->hasRole('admin'))

                        <div style="font-weight: 600">Article modifíkácia
                            <input type="text" name="mod_title" value="{{  old('mod_title') ?? $organization->mod_title }}" placeholder="Meno pred názvom článku">
                        </div>

                        <span style="font-weight: 600">Organizácia na predný zoznam</span><br>
                        @forelse(\App\Updater::all() as $updater)
                            @if( $updater->type == 'frontUser')
                                <input type="checkbox" name="updaters[]" value="{{ $updater->id }}"
                                       @foreach($organization['updaters'] as $up)
                                       @if($up->pivot->updater_id == $updater->id )
                                       checked
                                       @endif
                                       Žiadne položky
                                        @endforeach
                                >
                                {{ $updater->title }}<br>
                            @endif
                        @empty
                            žiadny tag
                        @endforelse

                        <span style="font-weight: 600">Zaradená do zoznamu</span><br>
                        @forelse(\App\Updater::all() as $updater)
                            @if( $updater->type == 'list')
                                <input type="checkbox" name="updaters[]" value="{{ $updater->id }}"
                                       @foreach($organization['updaters'] as $up)
                                       @if($up->pivot->updater_id == $updater->id )
                                       checked
                                       @endif
                                       Žiadne položky
                                        @endforeach
                                >
                                {{ $updater->title }}<br>
                            @endif
                        @empty
                            žiadny tag
                        @endforelse


                        <span style="font-weight: 600">Vyhľadávanie podľa mena v týždni</span><br>
                        @forelse(\App\Updater::all() as $updater)
                            @if( $updater->type == 'dayOfWeek')
                                <input type="checkbox" name="updaters[]" value="{{ $updater->id }}"
                                       @foreach($organization['updaters'] as $up)
                                       @if($up->pivot->updater_id == $updater->id )
                                       checked
                                       @endif
                                       Žiadne položky
                                        @endforeach
                                >
                                {{ $updater->title }}<br>
                            @endif
                        @empty
                            žiadny tag
                        @endforelse
                    @endif

                    <button type="submit" class="btn btn-small">Uložiť</button>


                    </div>
                </form>
            </div>

            <div class="page-aside">

            </div>


        </div>

    </div>


    @endsection

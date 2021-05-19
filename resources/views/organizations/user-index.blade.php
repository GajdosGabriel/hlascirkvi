@extends('layouts.app')

@section('content')

    @include('organizations._profil-menu')
    <div class="page">

        <div class="flex">

            <div class="md:w-8/12 md:mr-5">

                <h3 class="page_title">Vaše kanály</h3>

                @include('organizations._organization-table')

            </div>

            <div class="w-4/12">

                <new-organization inline-template>
                    <div class="w-full">
                        <h4 style="margin: 2rem 0rem; cursor: pointer" @click="toggle">Nová organizácia
                            <i v-if="!showForm" class="far fa-plus-square"></i>
                            <i v-if="showForm" class="far fa-minus-square"></i>
                        </h4>

                        <form method="post" action="{{ route('organization.store', [auth()->user()->id] ) }}"
                              v-if="showForm">
                            @csrf
                            <div class="form-group">
                                <label>Meno novej organizácie</label>
                                <input type="text" name="title" class="form-control" placeholder="Názov organizácie" required>
                            </div>

                            <div class="form-group">
                                <label>Ulica a číslo</label>
                                <input type="text" name="street" class="form-control" placeholder="Ulica a číslo">
                            </div>

                            <div class="form-group">
                                <label>Mesto</label>
                                <input type="text" name="city" class="form-control" placeholder="Mesto">
                            </div>

                            <div class="form-group">
                                <label>Región</label>
                                <select name="region_id" class="form-control input-sm" required>
                                    <option label="-- Vybrať --"></option>
                                    <option value="1">Bratislavský</option>
                                    <option value="2">Trenčianský</option>
                                    <option value="4">Žilinský</option>
                                    <option value="5">Prešovský</option>
                                    <option value="6">Košický</option>
                                    <option value="7">Banskobystrický</option>
                                    <option value="3">Trnavský</option>
                                    <option value="8">Nitrianský</option>
                                    <option value="9">Neuvedené</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Telefón</label>
                                <input type="number" name="phone" class="form-control" placeholder="Potrebné v prípade vytvorenia akcie">
                            </div>


                            <span style="font-weight: 600">Zaradená do zoznamu</span><br>
                            @forelse(\App\Updater::all() as $updater)
                                @if( $updater->type == 'denomination')
                                    <input type="radio" required name="updaters[]" value="{{ $updater->id }}">
                                    {{ $updater->title }}<br>
                                @endif
                            @empty
                                žiadny tag
                            @endforelse


                            <div class="form-group" style="text-align: right">
                                <button class="btn btn-small">Uložiť</button>
                            </div>

                        </form>
                    </div>
                </new-organization>
                {{--                <new-organization :user="{{ auth()->user() }}"></new-organization>--}}

            </div>


        </div>

    </div>


@endsection

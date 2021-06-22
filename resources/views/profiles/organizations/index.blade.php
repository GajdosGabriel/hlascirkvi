@extends('layouts.app')

@section('content')
    <div class="grid grid-cols-12 gap-6  ">

        <div class="grid col-span-2  min-h-screen">
            <div class="flex flex-col bg-gray-200">

                @include('profiles._profil-menu')

            </div>
        </div>


        <div class="col-span-8">

            <div class="flex justify-between">

                <h3 class="page_title text-2xl">Vaše kanály</h3>

                <new-organization inline-template>
                    <div>
                        <h4 style="margin: 2rem 0rem; cursor: pointer" @click="toggle">Nová organizácia
                            <i v-if="!showForm" class="far fa-plus-square"></i>
                            <i v-if="showForm" class="far fa-minus-square"></i>
                        </h4>

                        <form method="post" action="{{ route('organizations.store', [auth()->user()->id]) }}"
                            v-if="showForm">
                            @csrf
                            <div class="form-group">
                                <label>Meno novej organizácie</label>
                                <input type="text" name="title" class="form-control" placeholder="Názov organizácie"
                                    required>
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
                                <input type="number" name="phone" class="form-control"
                                    placeholder="Potrebné v prípade vytvorenia akcie">
                            </div>


                            <span style="font-weight: 600">Zaradená do zoznamu</span><br>
                            @forelse(\App\Updater::all() as $updater)
                                @if ($updater->type == 'denomination')
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
                {{-- <new-organization :user="{{ auth()->user() }}"></new-organization> --}}


            </div>


            @include('organizations._organization-table')

            <div class="md:block flex justify-center my-8">
                {{ $organizations->links() }}
            </div>

        </div>

    </div>


@endsection

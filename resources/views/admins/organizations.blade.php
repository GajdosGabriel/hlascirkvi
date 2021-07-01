@extends('layouts.app')

@section('content')


    @component('layouts.pages.admin')
        @slot('page')

            <div class="col-span-9">

                @component('layouts.pages.page_title')
                @slot('title')

                Organizácie

                @endslot

                @slot('title_right')

                <new-organization inline-template>
                    <div>
                        <h4 style="margin: 2rem 0rem; cursor: pointer" @click="toggle">Nová organizácia
                            <i v-if="!showForm" class="far fa-plus-square"></i>
                            <i v-if="showForm" class="far fa-minus-square"></i>
                        </h4>

                        <form method="post" action="{{ route('organizations.store', [auth()->user()->id]) }}" v-if="showForm">
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
                                <label for="">Mesto</label>
                                <select name="village_id" class="form-control input-sm">
                                    <option label="{{ trans('web.select') }}"></option>

                                    @foreach(\App\Village::all() as $village)
                                    <option value="{{ $village->id }}">{{ $village->fullname }}  {{ $village->zip }}</option>
                                    @endforeach
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

                @endslot
            @endcomponent



                <div>
                    @include('organizations._organization-table')
                </div>

                <div class="md:block flex justify-center my-8">
                    {{ $organizations->links() }}
                </div>
            </div>

        @endslot
    @endcomponent


@endsection

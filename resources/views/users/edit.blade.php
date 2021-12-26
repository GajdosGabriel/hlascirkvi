@extends('layouts.app')

@section('content')
    <div class="grid grid-cols-12 gap-6  ">

        @include('profiles._profil-menu')


        <div class="col-span-10 content-start">

            @component('components.pages.page_title')
                @slot('title')

                    Upraviť uzívateľa

                @endslot

                @slot('title_right')

                @endslot
            @endcomponent


            <h3 class="font-semibold"></h3>

            <form method="post" action="{{ route('user.update', [$user->id]) }}">
                @csrf @method('PUT')
                <div class="card-body" style="width: 50%">

                    <div class="form-group">
                        <label for="first_name">Meno</label>
                        <input type="text" name="first_name" id="first_name" value="{{ $user->first_name }}"
                            class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Meno</label>
                        <input type="text" name="last_name" id="last_name" value="{{ $user->last_name }}"
                            class="form-control" required>
                    </div>


                    <button type="submit" class="btn btn-primary">Uložiť</button>
            </form>
        </div>

        <div class="page-aside">

        </div>


    </div>

    </div>


@endsection

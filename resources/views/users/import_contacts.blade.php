@extends('layouts.app')

@section('content')

    <div class="container">

        <div class="page">

            <div class="page-content">

                <div>
                    <a class="tag" href="{{  URL::previous() }}">Späť</a>
                </div>

                <div class="level">
                    <h3> {{ $user->fullName }}</h3>
                </div>

                <h4>Import kontaktov do adresára</h4>
                <form action="{{ route('addresBook.storeUsersContact', [ auth()->id()]) }}" method="POST"> @csrf

                    <div class="form-group">
                        <textarea style="width: 100%" rows="5" name="body" placeholder="Sem nakopírujte textový súbor z ktorého sa vyextrahujú emaily" required></textarea>
                    </div>

                    <div class="form-group">
                        <button class="btn btn-small">Importovať kontakty</button>
                    </div>

                </form>

                @forelse($users as $user)
                    <div>
{{--                        {{ ($users->currentpage()-1) * $users ->perpage() + $loop->index + 1 }} - {{ $user->fullname }} ---}}
                        {{ $user->email }}</div>
                    @empty
                    <p>Bez záznamu</p>
                @endforelse

                {{ $users->render() }}


            </div>

            <div class="page-aside">


            </div>


        </div>

    </div>


    @endsection
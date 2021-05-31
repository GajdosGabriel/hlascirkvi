@extends('layouts.app')

@section('content')
    <div class="grid grid-cols-12 gap-6  ">

        <div class="grid col-span-2  min-h-screen">
            <div class="flex flex-col bg-gray-200">

                @include('profiles._profil-menu')

            </div>
        </div>


        <div class="grid col-span-10 p-5">

            <h2 class="page_title">Import kontaktov</h2>

            <h4>Import kontaktov do adresára</h4>
            <form action="{{ route('addresBook.storeUsersContact', [auth()->id()]) }}" method="POST"> @csrf

                <div class="form-group">
                    <textarea style="width: 100%" class="form-control" rows="5" name="body"
                        placeholder="Sem nakopírujte textový súbor z ktorého sa vyextrahujú emaily" required></textarea>
                </div>

                <div class="form-group">
                    <button class="btn btn-small">Importovať kontakty</button>
                </div>

            </form>

            @forelse($users as $user)
                <div>
                    {{-- {{ ($users->currentpage()-1) * $users ->perpage() + $loop->index + 1 }} - {{ $user->fullname }} - --}}
                    {{ $user->email }}</div>
            @empty
                <p>Záznam kontaktov je prázdny</p>
            @endforelse

            {{ $users->render() }}


        </div>

        <div class="page-aside">


        </div>


    </div>

    </div>


@endsection
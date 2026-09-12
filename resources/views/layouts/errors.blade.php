{{-- Pohľad, ktorý si chyby vypisuje sám pri jednotlivých políčkach (napr.
     auth/register), si vyhlási sekciu `own-errors` a tento súhrnný zoznam sa
     preň preskočí — inak by tú istú hlášku videl návštevník dvakrát. --}}
@if ($errors->any() && ! View::hasSection('own-errors'))
    <div class="mt-2">
        <ul>
            @foreach ($errors->all() as $error)
                <li class="border-2 border-red-700 bg-red-500 rounded-md text-gray-200 p-1 text-center mb-2">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


@if (session('error'))
    <div class="border-4 border-red-700 bg-red-500 rounded-md text-gray-200 p-4 text-3xl text-center">
        {{ session('error') }}
    </div>
@endif

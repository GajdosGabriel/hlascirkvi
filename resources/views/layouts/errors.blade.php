{{-- Formuláre s chybami pri políčkach neukazujú duplicitný súhrn. --}}
@if ($errors->any() && ! View::hasSection('own-errors'))
    <div class="mx-auto my-4 max-w-5xl px-4" role="alert">
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
            <p class="mb-2 font-semibold">Skontrolujte zadané údaje.</p>
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

@if (session('error'))
    <div class="mx-auto my-4 max-w-5xl px-4" role="alert">
        <p class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">{{ session('error') }}</p>
    </div>
@endif

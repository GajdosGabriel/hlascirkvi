@if ($errors->any())
    <div class="alert">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


@if (session('error'))
    <div class="border-4 border-red-700 bg-red-500 rounded-md text-gray-200 p-4 text-3xl text-center">
        {{ session('error') }}
    </div>
@endif

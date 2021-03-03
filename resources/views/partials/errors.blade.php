@if (count($errors) > 0)

        <ul class="bg-red-700 text-white p-4 border-3 border-white">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>

@endif

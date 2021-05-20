@if (auth()->user()->hasRole('admin'))
    <div class="flex mb-5 cursor-pointer">

        {{-- <a class="tag" href="{{ route('organization.profile', [auth()->id(), auth()->user()->slug]) }}">Profil</a> --}}
        <a class="border-2 flex-1  border-gray-100 hover:bg-indigo-600 hover:text-gray-200 w-full bg-indigo-300 text-gray-900 text-center px-2"
            href="{{ route('admin.organization.index') }}">Všetky organizácie</a>
        <a class="border-2 flex-1  border-gray-100 hover:bg-indigo-600 hover:text-gray-200 w-full bg-indigo-300 text-gray-900 text-center px-2"
            href="{{ route('admin.unpublished') }}">Buffer</a>
        <a class="border-2 flex-1  border-gray-100 hover:bg-indigo-600 hover:text-gray-200 w-full bg-indigo-300 text-gray-900 text-center px-2"
            href="{{ route('admin.statistic', ['days' => 1]) }}">Štatistika</a>
        <a class="border-2 flex-1  border-gray-100 hover:bg-indigo-600 hover:text-gray-200 w-full bg-indigo-300 text-gray-900 text-center px-2"
            href="{{ route('admin.user.index') }}">Užívatelia</a>

    </div>
@endif

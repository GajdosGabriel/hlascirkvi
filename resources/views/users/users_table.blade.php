{{-- <input class="text-gray-700 p-1 border-2 border-gray-700 rounded-sm md:w-3/4"
            placeholder="Search by name, email ..." type="text"> --}}

<table class="table-auto border-2 border-gray-400 rounded-md w-full">
    <thead class="bg-gray-500 text-white">
        <tr>
            <th>Id</th>
            <th>Názov</th>
            <th>Email</th>
            <th>5. pád</th>
            <th>Blokovaný</th>
            <th>Overené</th>
            <th>Denominácia</th>
            <th>Registrácia</th>
            <th>Akcia</th>
        </tr>
    </thead>

    <tbody>
        @forelse($users as $user)
            <tr class="border-2 border-gray-300">
                <td class="px-2">{{ $user->id }} </td>
                <td class="px-2">
                    <div class="font-semibold">{{ $user->first_name }} {{ $user->last_name }}</div>
                </td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->vocative }}</td>
                <td class="text-center ">
                    @if ($user->disabled)
                        <span class="bg-red-600 text-gray-200 px-2 rounded-md">{{ $user->disabled }}</span>
                    @else
                        {{ $user->disabled }}
                    @endif
                </td>
                <td title="{{ $user->email_verified_at }}">
                    @if ($user->email_verified_at)
                        ano
                    @endif
                </td>
                <td>{{ $user->set_denomination }}</td>
                <td class="text-sm">{{ $user->created_at->diffForHumans() }}</td>
                <td class="px-2">
                    <a href="{{ route('admin.users.edit', [$user->id]) }}">
                        <i title="Upraviť" class="fas fa-edit"></i>
                    </a>
                </td>
            </tr>
        @empty
            <table>
                <thead>
                    <tr>Bez záznamu</tr>
                </thead>
            </table>
        @endforelse
    </tbody>
</table>

<div class="">

    <h3>Registrovaný užívatelia</h3>
    <input class="text-gray-700 p-3 m-4 border-2 border-gray-700 rounded-sm md:w-3/4" placeholder="Search by name, email ..." type="text" wire:model="search">

    <table>
        <thead>
        <tr>
            <th>Por./Id</th>
            <th>Názov</th>
            <th>Email</th>
            <th>5. pád</th>
            <th>Vypnuté</th>
            <th>Overené</th>
            <th>Denominácia</th>
            <th>Registrácia</th>
            <th>Akcia</th>
        </tr>
        </thead>

        <tbody>
        @forelse($users as $user)
            <tr>
                <td>{{ $users->count() - $loop->iteration +1 }} / {{ $user->id }} </td>
                <td class="whitespace-no-wrap">
                    <a href="{{ route('organization.edit', [ $user->id, $user->slug]) }}">
                        {{ $user->first_name }} {{ $user->last_name }}
                    </a>
                </td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->vocative }}</td>
                <td>{{ $user->disabled }}</td>
                <td>{{ $user->verified }}</td>
                <td>{{ $user->set_denomination }}</td>
                <td>{{ $user->created_at->diffForHumans() }}</td>
                <td>
                    <a href="{{ route('organization.edit', [ $user->id, $user->slug]) }}">
                        <i title="Upraviť" class="fas fa-edit"></i>
                    </a>
                </td>
            </tr>
        @empty
            <table><thead><tr>Bez záznamu</tr></thead></table>
        @endforelse
        </tbody>
    </table>

</div>

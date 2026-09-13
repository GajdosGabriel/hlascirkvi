{{-- <input class="text-gray-700 p-1 border-2 border-gray-700 rounded-sm md:w-3/4"
            placeholder="Search by name, email ..." type="text"> --}}

<x-dashboard.table label="Registrovaní používatelia">
    <thead class="bg-gray-500 text-white">
        <tr>
            <th>Id</th>
            <th>Názov</th>
            <th>Email</th>
            <th>Stav</th>
            <th>Overené</th>
            <th>Denominácia</th>
            <th>Registrácia</th>
            <th>Posledné prihlásenie</th>
            <th>Spôsob</th>
            <th>Akcia</th>
        </tr>
    </thead>

    <tbody>
        @forelse($users as $user)
            <tr class="border-2 border-gray-300">
                <td class="td">{{ $user->id }} </td>
                <td class="td">
                    <div class="font-semibold">{{ $user->first_name }} {{ $user->last_name }}</div>
                </td>
                <td class="max-w-[14rem] break-all">{{ $user->email }}</td>
                <td class="text-center" title="{{ $user->status_reason }}">
                    <span class="px-2 py-1 rounded-md text-xs font-semibold {{ $user->status->isActive() ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $user->status->label() }}
                    </span>
                </td>
                <td title="{{ $user->email_verified_at }}">
                    @if ($user->email_verified_at)
                        ano
                    @endif
                </td>
                <td class="max-w-[14rem] break-words">{{ $user->set_denomination }}</td>
                <td class="text-sm">{{ $user->created_at->diffForHumans() }}</td>
                <td class="text-sm" title="{{ $user->last_login_at?->format('d.m.Y H:i:s') }}">
                    @if ($user->last_login_at)
                        {{ $user->last_login_at->diffForHumans() }}
                        @if ($user->last_login_ip)
                            <div class="text-xs text-gray-500">{{ $user->last_login_ip }}</div>
                        @endif
                    @else
                        Nikdy
                    @endif
                </td>
                <td class="text-sm">{{ $user->last_login_via_label ?? '—' }}</td>
                <td class="td">
                    <dropdown-slot><a href="{{ route('admin.user.edit', [$user->id]) }}">
                        <i class="fas fa-edit" aria-hidden="true"></i> Upraviť
                    </a></dropdown-slot>
                </td>
            </tr>
        @empty
            <tr><td colspan="10"><x-dashboard.empty>Bez záznamu</x-dashboard.empty></td></tr>
        @endforelse
    </tbody>
</x-dashboard.table>

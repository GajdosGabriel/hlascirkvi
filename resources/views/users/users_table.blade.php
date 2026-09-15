{{-- <input class="text-gray-700 p-1 border-2 border-gray-700 rounded-sm md:w-3/4"
            placeholder="Search by name, email ..." type="text"> --}}

<x-dashboard.table label="Registrovaní používatelia">
    <thead class="bg-gray-500 text-white">
        <tr>
            @php
                // Klik na Id prepína vzostupne/zostupne; predvolene je výpis od najnovších.
                $idAsc = request('sort') === 'id';
            @endphp
            <th aria-sort="{{ request('sort') === 'id' ? 'ascending' : (request('sort') === '-id' ? 'descending' : 'none') }}">
                <a href="{{ request()->fullUrlWithQuery(['sort' => $idAsc ? '-id' : 'id', 'page' => null]) }}"
                   class="inline-flex items-center gap-1 hover:underline" style="color: inherit">
                    Id
                    <i class="fas {{ $idAsc ? 'fa-sort-up' : (request('sort') === '-id' ? 'fa-sort-down' : 'fa-sort') }}" aria-hidden="true"></i>
                </a>
            </th>
            <th>Názov</th>
            <th>Email</th>
            <th>Stav</th>
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
                <td class="text-center">
                    <x-dashboard.status-badge :badge="$user->accountBadge()" />
                </td>
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
                    <dropdown-slot label="Spravovať používateľa">
                        <a href="{{ route('admin.user.edit', [$user->id]) }}">
                            <i class="fas fa-pen" aria-hidden="true"></i> Upraviť
                        </a>
                    </dropdown-slot>
                </td>
            </tr>
        @empty
            <tr><td colspan="8"><x-dashboard.empty>Bez záznamu</x-dashboard.empty></td></tr>
        @endforelse
    </tbody>
</x-dashboard.table>

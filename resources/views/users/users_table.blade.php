{{-- <input class="text-gray-700 p-1 border-2 border-gray-700 rounded-sm md:w-3/4"
            placeholder="Search by name, email ..." type="text"> --}}

<x-dashboard.table label="Registrovaní používatelia">
    <thead class="bg-gray-500 text-white">
        <tr>
            @php
                // Klik na hlavičku radí podľa stĺpca (UserFilters::SORTS), ďalší klik
                // obráti smer. Dátumy začínajú od najnovších, texty od A.
                // Predvolene je výpis od najnovších registrácií.
                $columns = [
                    'id' => ['Id', false],
                    'name' => ['Názov', false],
                    'email' => ['Email', false],
                    'status' => ['Stav', false],
                    'created' => ['Registrácia', true],
                    'login' => ['Posledné prihlásenie', true],
                    'via' => ['Spôsob', false],
                ];
                $currentSort = (string) request('sort');
            @endphp
            @foreach ($columns as $key => [$label, $descFirst])
                @php
                    $asc = $currentSort === $key;
                    $desc = $currentSort === '-' . $key;
                    $next = $asc ? '-' . $key : ($desc ? $key : ($descFirst ? '-' . $key : $key));
                @endphp
                <th aria-sort="{{ $asc ? 'ascending' : ($desc ? 'descending' : 'none') }}">
                    <a href="{{ request()->fullUrlWithQuery(['sort' => $next, 'page' => null]) }}"
                       class="inline-flex items-center gap-1 whitespace-nowrap hover:underline" style="color: inherit">
                        {{ $label }}
                        <i class="fas {{ $asc ? 'fa-sort-up' : ($desc ? 'fa-sort-down' : 'fa-sort opacity-50') }}" aria-hidden="true"></i>
                    </a>
                </th>
            @endforeach
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
